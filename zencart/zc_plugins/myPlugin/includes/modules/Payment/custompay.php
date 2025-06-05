<?php
// custompay.php contains core logic that tells zencart how payment gateway works.

class custompay extends base {
    public $code, $title, $description, $enabled;

    // __construct() - special method that initializes new object of payment module class
    // automatically called when object of class is created
    public function __construct() {
        global $order;
        $this->code = 'custompay'; // unique identifier used throughout zencart 
        $this->title = MODULE_PAYMENT_CUSTOMPAY_TEXT_TITLE; // title display name shown to customers
        $this->description = MODULE_PAYMENT_CUSTOMPAY_TEXT_DESCRIPTION; //detailed description of admin panel
        $this->sort_order = MODULE_PAYMENT_CUSTOMPAY_SORT_ORDER; // controls display order in checkout
        $this->enabled = (MODULE_PAYMENT_CUSTOMPAY_STATUS == 'True'); // whether module is enabled or not
        $this->form_action_url = zen_href_link(FILENAME_CHECKOUT_PROCESS, '', 'SSL'); // URL to which form submits data
    }

    function update_status() {
        global $order;
        if (($this->enabled == true) && ((int)MODULE_PAYMENT_CUSTOMPAY_ZONE > 0)) {
            $check_flag = false;   // $check_flag is used to mark that a condition has been passed  
            $check_query = $GLOBALS['db']->Execute(
                "SELECT zone_id FROM " . TABLE_ZONES_TO_GEO_ZONES . "
                WHERE geo_zone_id = '" . MODULE_PAYMENT_CUSTOMPAY_ZONE . "'
                AND zone_country_id = '" . $order->billing['country']['id'] . "'"
            );
            while (!$check_query->EOF) { // EOF- End Of File or End of fetch check whether result set has reached end
                if($check_query->fields['zone_id'] < 1 || $check_query->fields['zone_id'] == $order->billing['zone_id']) {
                    $check_flag = true; // if zone_id is less than 1 or matches billing zone, set flag to true
                    break;
                }
                $check_query->MoveNext(); 
            }

            if ($check_flag == false) {
                $this->enabled = false; // if no matching zone found, disable the module
            }
        }
    }

    function javascript_validation() {
        return false; // no client side validation needed
    }

    function selection() {
        return [
            'id' => $this->code, 
            'module' => $this->title,
        ];
    }

    function pre_confirmation_check() {
        return false; // no pre-confirmation checks needed for this module
    }

    function confirmation() {
        return false; // no confirmation needed for this module
    }

    function process_button() {
    // This method should return an empty string if no data needs to be submitted via form
    return ''; // hidden fields are not needed because before_process() handles all data
}

    function before_process() {
        global $order;

        // Read PaySecure credentials from store's configuration:
        $merchantId = MODULE_PAYMENT_CUSTOMPAY_MERCHANT_ID;
        $apiKey = MODULE_PAYMENT_CUSTOMPAY_API_KEY;

        // prepare payment data payload according to PaySecure's API format:
        $paymentData = [
            'merchant_id' => $merchantId,
            'amount' => $order->info['total'],
            'currency' => $order->info['currency'], 
            'customer_name' => $order->billing['firstname'] . ' ' . $order->billing['lastname'], 
            'customer_email' => $order->customer['email_address'],
            'order_id' => date('YmdHis'),  // YmdHis - for year, month, day, hour, minutes, seconds format for unique order ID 
        ];

        // Convert payment data to JSON
        $payload = json_encode($paymentData);

        // Initialize cURL session
       $ch = curl_init('https://api.paysecure.com/v1/charge');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json', 
            'Authorization: Bearer ' . $apiKey
        ]);
        
        // Execute cURL request
        $response = curl_exec($ch);

        // check for cURL errors
        // curl_errno() is a PHP function that returns error number for last cURL operation. used to check if any errors occured during cURL request.
        if (curl_errno($ch)) {
            $error_message = curl_error($ch); 
            curl_close($ch);
            zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . urlencode('Payment request failed'), 'SSL')); // redirect to payment page with error message if cURL fails 
        }

        // Decode JSON response from PaySecure
        $response_data = json_decode($response, true); // decode JSON response from gateway
        curl_close($ch); // close cURL session

        // check if payment was unsuccessful
        if (empty($response_data['success']) || !$response_data['success']) {
            $error = $response_data['message'] ?? 'Payment failed';
            zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, 'error_message=' . urlencode($error), 'SSL')); // redirect to payment page with error message if payment failed
        }

        // If payment requires frontend redirection (to a hosted checkout page)
        if (!empty($response_data['redirect_url'])) {
            // save order/session state if needed
            $_SESSION['custompay_transaction_id'] = $response_data['transaction_id'] ?? null; 
            
            // Redirect customer to gateway-hosted page
            zen_redirect($response_data['redirect_url']);
            exit; // to end script execution after redirect
        }
       
        // If no redirection, assume payment is completed
        $_SESSION['custompay_transaction_id'] = $response_data['transaction_id'] ?? null; // store transaction ID in session for later use

        return true;
    }

    function after_process() {
        // Access order ID and order object
        global $insert_id, $order, $db; // $insert_id is the ID of the order just created, $order contains order details, $db is database connection object;

        // get transaction ID using null coalescing operator (??)
        // Returns null if $_SESSION['custompay_transaction_id'] doesn't exist 
        $transaction_id = $_SESSION['custompay_transaction_id'] ?? null; // retrieve transaction ID from session, if exists

        // If we have a transaction ID, store it
        if ($transaction_id) {
            $db->Execute("INSERT INTO custompay_transactions (order_id, transaction_id) VALUES ('$insert_id', '" . zen_db_input($transaction_id) . "')"); // store transaction ID in custompay_transaction table
        }
        return false;
    }

    function get_error() {
        return [
            'title' => MODULE_PAYMENT_CUSTOMPAY_TEXT_ERROR, 
            'error' => $_GET['error_message'] ?? 'Unknown error occured during payment processing.' 
        ]; // return error message if payment fails   
    }

    // check() method verifies if payment module is properly installed in database by checking its configuration settings.
    function check() {
        global $db; //global used to access global variables from within a function or method scope.
        $check = $db->Execute("SELECT configuration_value FROM " . TABLE_CONFIGURATION . " WHERE configuration_key = 'MODULE_PAYMENT_CUSTOMPAY_STATUS'");
        return ($check->RecordCount() > 0); // check if module installed by checking if configuration key exists
    }

    function install() {
        // left blank as sql is handled by manifest
    }

    function remove() {
        global $db;
        $db->Execute("DELETE FROM " . TABLE_CONFIGURATION . " WHERE configuration_key LIKE 'MODULE_PAYMENT_CUSTOMPAY_%'");
    }

    function keys() {
        return [
            'MODULE_PAYMENT_CUSTOMPAY_STATUS',
            'MODULE_PAYMENT_CUSTOMPAY_ZONE',
            'MODULE_PAYMENT_CUSTOMPAY_SORT_ORDER'
        ];
    }
}


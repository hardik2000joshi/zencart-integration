INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added)
VALUES
('Enable CustomPay Module', 'MODULE_PAYMENT_CUSTOMPAY_STATUS', 'TRUE', 'Do you want to accept CustomPay payments?', 6, 0, 'zen_cfg_select_option(array(\'TRUE\', \'FALSE\'), ', now());

INSERT INTO configuration(configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added)
VALUES
('Payment Zone', 'MODULE_PAYMENT_CUSTOMPAY_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', 6, 2, 'zen_cfg_pull_down_zone_classes(', now());

INSERT INTO configuration (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added)
VALUES
('Sort Order', 'MODULE_PAYMENT_CUSTOMPAY_SORT_ORDER', '0', 'sorted order of display. Lowest is displayed first.', 6, 3, NULL, now());

CREATE TABLE IF NOT EXISTS custompay_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    transaction_id VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO configuration(configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added)
VALUES 
('PaySecure Merchant ID', 'MODULE_PAYMENT_CUSTOMPAY_MERCHANT_ID', 'your_merchant_id_here', 'Your PaySecure merchant ID', 6, 4, now());

INSERT INTO configuration(configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added)
VALUES
('PaySecure API Key', 'MODULE_PAYMENT_CUSTOMPAY_API_KEY', 'your_api_key_here', 'Your PaySecure API Key or Secret', 6, 5, now());


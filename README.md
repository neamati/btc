# BTC Payment Gateway for WHMCS

This is a custom payment gateway module for WHMCS that allows merchants to accept Bitcoin (BTC) payments using the Binance API. The module provides a seamless integration for cryptocurrency payments, ensuring a smooth experience for both merchants and customers.

## Features

- **Bitcoin Payments**: Accept Bitcoin payments directly into your wallet.
- **Real-Time Exchange Rates**: Fetches the latest BTC to USD exchange rates using the Binance API.
- **Sales Tax Support**: Automatically calculates and includes sales tax in the BTC amount.
- **QR Code Generation**: Generates a QR code for easy payment by customers.
- **Transaction Verification**: Verifies BTC transactions on the Binance network.
- **Customizable Payment Status**: Allows admins to define the default payment status for transactions.
- **Responsive Design**: Fully responsive payment interface for desktop and mobile users.
- **Support Ticket Integration**: Provides a link for customers to open a support ticket if payment issues arise.

## Installation

Follow these steps to install and configure the BTC Payment Gateway module in WHMCS:

### Step 1: Upload the Module
1. Download the module files from this repository.
2. Copy the `btc` folder to the following directory in your WHMCS installation:
   ```
   /modules/gateways/
   ```

### Step 2: Activate the Module
1. Log in to your WHMCS Admin Panel.
2. Navigate to **Setup** > **Payments** > **Payment Gateways**.
3. Click on the **All Payment Gateways** tab.
4. Locate the "BTC Payment Gateway" and click **Activate**.

### Step 3: Configure the Module
1. After activation, click on the **Configure** button next to the BTC Payment Gateway.
2. Fill in the required fields:
   - **BTC Wallet Address**: Enter your Bitcoin wallet address.
   - **Sales Tax Percentage**: Specify the sales tax percentage (default is 5.0%).
   - **Binance API Key**: Enter your Binance API key.
   - **Binance Secret Key**: Enter your Binance secret key.
   - **Default Payment Status**: Choose the default status for transactions (e.g., Paid, Payment Pending).
3. Save the changes.

### Step 4: Test the Module
1. Create a test invoice in WHMCS.
2. Select the BTC Payment Gateway as the payment method.
3. Verify that the payment page displays the BTC amount, QR code, and wallet address.
4. Make a test payment and ensure the transaction is verified and the invoice status is updated correctly.

## Usage

Once installed and configured, customers can select the BTC Payment Gateway during checkout. The module will display the BTC amount, wallet address, and a QR code for payment. The system will automatically verify the transaction and update the invoice status.

## Troubleshooting

- **Failed to Fetch BTC Rate**: Ensure your Binance API key and secret key are correctly configured.
- **Transaction Not Verified**: Check the wallet address and expected amount for accuracy.
- **Support Ticket Message**: If a payment is delayed, customers can open a support ticket using the provided link.

## Support

For any issues or questions, please contact our support team at [support@example.com](mailto:support@example.com).

## License

This project is licensed under the MIT License. See the LICENSE file for details.
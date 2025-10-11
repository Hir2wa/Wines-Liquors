# SMS Integration Guide for Rwanda

## Option 1: Africa's Talking (Recommended)

### 1. Sign up at Africa's Talking
- Go to: https://africastalking.com/
- Create an account
- Get your API key and username

### 2. Install the SDK
```bash
composer require africastalking/africastalking
```

### 3. Update User.php to send real SMS

```php
// Add to User.php
use AfricasTalking\SDK\AfricasTalking;

public function sendSMSVerification($phone, $code) {
    try {
        // Initialize Africa's Talking
        $username = 'your_username'; // Your Africa's Talking username
        $apiKey = 'your_api_key';    // Your Africa's Talking API key
        
        $AT = new AfricasTalking($username, $apiKey);
        $sms = $AT->sms();
        
        // Format phone number
        $formattedPhone = $this->formatPhoneNumber($phone);
        
        // Send SMS
        $result = $sms->send([
            'to'      => $formattedPhone,
            'message' => "Your Wines & Liquors verification code is: {$code}. Valid for 15 minutes."
        ]);
        
        if ($result['status'] === 'success') {
            return ['success' => true, 'message' => 'SMS sent successfully'];
        } else {
            return ['success' => false, 'message' => 'Failed to send SMS'];
        }
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'SMS error: ' . $e->getMessage()];
    }
}
```

## Option 2: Twilio

### 1. Sign up at Twilio
- Go to: https://www.twilio.com/
- Get your Account SID and Auth Token

### 2. Install Twilio SDK
```bash
composer require twilio/sdk
```

### 3. Update User.php

```php
use Twilio\Rest\Client;

public function sendSMSVerification($phone, $code) {
    try {
        $accountSid = 'your_account_sid';
        $authToken = 'your_auth_token';
        $twilioNumber = 'your_twilio_number';
        
        $client = new Client($accountSid, $authToken);
        
        $formattedPhone = $this->formatPhoneNumber($phone);
        
        $message = $client->messages->create(
            $formattedPhone,
            [
                'from' => $twilioNumber,
                'body' => "Your Wines & Liquors verification code is: {$code}. Valid for 15 minutes."
            ]
        );
        
        return ['success' => true, 'message' => 'SMS sent successfully'];
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'SMS error: ' . $e->getMessage()];
    }
}
```

## Option 3: MTN Mobile Money API (Rwanda Specific)

### 1. Contact MTN Rwanda
- Get API credentials from MTN
- Set up merchant account

### 2. Custom Integration
```php
public function sendSMSVerification($phone, $code) {
    try {
        $apiUrl = 'https://api.mtn.com/v1/sms/send';
        $apiKey = 'your_mtn_api_key';
        
        $data = [
            'to' => $this->formatPhoneNumber($phone),
            'message' => "Your Wines & Liquors verification code is: {$code}",
            'from' => 'WinesLiquors'
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            return ['success' => true, 'message' => 'SMS sent successfully'];
        } else {
            return ['success' => false, 'message' => 'Failed to send SMS'];
        }
        
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'SMS error: ' . $e->getMessage()];
    }
}
```

## Testing Without Real SMS Service

For development/testing, you can:

1. **Display code in browser** (current method)
2. **Log to server console** (current method)
3. **Send to email as backup**
4. **Use a test SMS service**

## Cost Considerations

- **Africa's Talking**: ~$0.05 per SMS
- **Twilio**: ~$0.0075 per SMS
- **MTN API**: Varies by agreement

## Recommendation

For Rwanda, I recommend **Africa's Talking** because:
- ✅ Good coverage in East Africa
- ✅ Reliable service
- ✅ Easy integration
- ✅ Competitive pricing





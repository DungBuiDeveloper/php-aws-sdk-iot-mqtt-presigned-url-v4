# php aws sdk iot mqtt presigned url v4

Connect Mqtt broker via Presigned Url v4

# Prepare

- IAM Polycy

```
{
    "Version": "2012-10-17",
    "Statement": [
        {
            "Effect": "Allow",
            "Action": [
                "iot:Connect",
                "iot:Publish",
                "iot:Subscribe",
                "iot:Receive"
            ],
            "Resource": "*"
        }
    ]
}
```

- IAM User for get access key and sceret key
- If you don't use lambda replace code in publish message function

```
$provider = CredentialProvider::defaultProvider();
$credential = $provider()->wait();
```

to

```
$credential = $this->getCredential();
```

# Test connect

- open file test.html
- set wss url on the presignedUrl variable
- You can test online with https://mqttx.app/web-client

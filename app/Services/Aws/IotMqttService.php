<?php
namespace App\Services\Aws;
use Aws\Credentials\CredentialProvider;
use Aws\Signature;
use GuzzleHttp\Psr7\Request;
use Carbon\Carbon;
use Aws\Credentials\Credentials;
use Aws\IotDataPlane\IotDataPlaneClient;
class IotMqttService
{
    /**
     * AWS IoT MQTT Variables
     */
    private $awsIotMqttEndpoint;
    private $awsIotMqttRegion;
    private $awsIotMqttAccessKey;
    private $awsIotMqttSecretKey;
    /**
     * Construct IotMqttService
     */
    public function __construct()
    {
        $this->awsIotMqttEndpoint = config('base.iot_mqtt_config.aws_iot_mqtt_endpoint');
        $this->awsIotMqttRegion = config('base.iot_mqtt_config.aws_iot_mqtt_region');
        $this->awsIotMqttAccessKey = config('base.iot_mqtt_config.aws_iot_mqtt_access_key');
        $this->awsIotMqttSecretKey = config('base.iot_mqtt_config.aws_iot_mqtt_secret_key');
    }
    /**
     * Get Credential For AWS IoT Gateway
     *
     * @return Credentials
     */
    private function getCredential(): Credentials
    {
        return new Credentials($this->awsIotMqttAccessKey, $this->awsIotMqttSecretKey);
    }
    /**
     * Generate AWS IoT MQTT URL for WebSocket
     *
     * @return string
     */
    public function generateAwsIotMqttUrl(): string
    {
        # Iot MQTT Params
        $expires = Carbon::now()->addDay()->getTimestamp();
        $credential = $this->getCredential();
        $iotMethod = config('base.iot_mqtt_config.presinged_url.method');
        $iotService = config('base.iot_mqtt_config.presinged_url.service');
        $iotRequestUrl = "wss://{$this->awsIotMqttEndpoint}/mqtt";
        # create request
        $request = new Request($iotMethod, $iotRequestUrl);
        # sign request with signature v4
        $sign = new Signature\SignatureV4($iotService, $this->awsIotMqttRegion);
        $signedRequest = $sign->presign($request, $credential, $expires);
        return (string) $signedRequest->getUri();
    }
    /**
     * Publish Message to AWS IoT MQTT
     *
     * @param string $topic
     * @param string $message
     *
     * @return bool
     */
    public function publishMessageToAwsIotMqtt($topic, $message): bool
    {
        $provider = CredentialProvider::defaultProvider();
        $credential = $provider()->wait();
        $version = 'latest';
        $iotClient = new IotDataPlaneClient([
            'region'  => $this->awsIotMqttRegion,
            'version' => $version,
            'credentials' => $credential,
            'endpoint' => "https://{$this->awsIotMqttEndpoint}",
        ]);
        $iotClient->publish([
            'topic' => $topic,
            'payload' => json_encode(['message' => $message]),
        ]);
        return true;
    }
}

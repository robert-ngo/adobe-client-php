<?php

declare(strict_types=1);

use Adobe\Client\Auth\BearerTokenProvider;
use Adobe\Client\Core\SdkConfig;
use Adobe\Client\Segmentation\SegmentationClient;
use Adobe\Client\Core\HttpClient;
use GuzzleHttp\Psr7\HttpFactory;

require dirname(__DIR__) . '/vendor/autoload.php';

// Configuration - Replace with your actual values or set environment variables
$baseUri = getenv('AEP_BASE_URI') ?: 'https://platform.adobe.io';
$accessToken = getenv('AEP_ACCESS_TOKEN') ?: '';
$apiKey = getenv('AEP_API_KEY') ?: '';
$imsOrgId = getenv('AEP_IMS_ORG_ID') ?: '';
$sandboxName = getenv('AEP_SANDBOX_NAME') ?: 'prod';

if (empty($accessToken) || empty($apiKey) || empty($imsOrgId)) {
    echo "Please set environment variables:\n";
    echo "  AEP_ACCESS_TOKEN - Your Adobe Experience Platform access token\n";
    echo "  AEP_API_KEY - Your API key (client ID)\n";
    echo "  AEP_IMS_ORG_ID - Your IMS organization ID\n";
    echo "  AEP_SANDBOX_NAME - Your sandbox name (default: prod)\n";
    exit(1);
}

try {
    // Set up HTTP stack using Guzzle
    $psr18Client = new GuzzleHttp\Client();
    $httpFactory = new HttpFactory();

    // Configure SDK with required headers for Adobe Experience Platform
    $headers = [
        'x-api-key' => $apiKey,
        'x-gw-ims-org-id' => $imsOrgId,
        'x-sandbox-name' => $sandboxName,
    ];

    $config = new SdkConfig($baseUri, 'adobe-client-php-example/1.0.0', $headers);
    $auth = new BearerTokenProvider($accessToken);

    // Create HTTP client and Segmentation client
    $httpClient = new HttpClient($psr18Client, $httpFactory, $httpFactory, $config, $auth);
    $segmentation = new SegmentationClient($httpClient);

    echo "Adobe Experience Platform Segmentation Service Example\n";
    echo str_repeat('=', 60) . "\n\n";

    // Example 1: List audiences
    echo "1. Listing audiences (first 5)...\n";
    $audiences = $segmentation->listAudiences(['limit' => 5, 'start' => 0]);
    echo "Found " . ($audiences['page']['totalCount'] ?? 0) . " total audiences\n";
    if (!empty($audiences['audiences'])) {
        foreach ($audiences['audiences'] as $audience) {
            echo "  - {$audience['name']} (ID: {$audience['id']})\n";
        }
    }
    echo "\n";

    // Example 2: List segment definitions
    echo "2. Listing segment definitions (first 5)...\n";
    $segments = $segmentation->listSegmentDefinitions(['limit' => 5, 'start' => 0]);
    echo "Found " . ($segments['page']['totalCount'] ?? 0) . " total segment definitions\n";
    if (!empty($segments['segments'])) {
        foreach ($segments['segments'] as $segment) {
            echo "  - {$segment['name']} (ID: {$segment['id']})\n";
        }
    }
    echo "\n";

    // Example 3: List segment jobs
    echo "3. Listing recent segment jobs (first 3)...\n";
    $jobs = $segmentation->listSegmentJobs(['limit' => 3, 'sort' => 'updateTime:desc']);
    if (!empty($jobs['children'])) {
        foreach ($jobs['children'] as $job) {
            $status = $job['status'] ?? 'UNKNOWN';
            $created = $job['createdTime'] ?? 'N/A';
            echo "  - Job ID: {$job['id']} | Status: $status | Created: $created\n";
        }
    } else {
        echo "  No segment jobs found\n";
    }
    echo "\n";

    // Example 4: List export jobs
    echo "4. Listing export jobs (first 3)...\n";
    $exportJobs = $segmentation->listExportJobs(['limit' => 3, 'offset' => 0]);
    if (!empty($exportJobs['records'])) {
        foreach ($exportJobs['records'] as $exportJob) {
            $status = $exportJob['status'] ?? 'UNKNOWN';
            $created = $exportJob['createdTime'] ?? 'N/A';
            echo "  - Export Job ID: {$exportJob['id']} | Status: $status | Created: $created\n";
        }
    } else {
        echo "  No export jobs found\n";
    }
    echo "\n";

    // Example 5: Create a simple audience (commented out to avoid creating test data)
    /*
    echo "5. Creating a new audience...\n";
    $newAudience = $segmentation->createAudience([
        'name' => 'Test Audience - US Customers',
        'description' => 'Customers from the United States',
        'type' => 'SegmentDefinition',
        'expression' => [
            'type' => 'PQL',
            'format' => 'pql/text',
            'value' => 'homeAddress.country = "US"',
        ],
        'schema' => [
            'name' => '_xdm.context.profile',
        ],
    ]);
    echo "Created audience: {$newAudience['name']} (ID: {$newAudience['id']})\n\n";

    // Get the newly created audience
    echo "6. Retrieving the created audience...\n";
    $retrievedAudience = $segmentation->getAudience($newAudience['id']);
    echo "Retrieved: {$retrievedAudience['name']}\n";
    echo "Expression: {$retrievedAudience['expression']['value']}\n\n";

    // Update the audience description
    echo "7. Updating audience description...\n";
    $updatedAudience = $segmentation->patchAudience($newAudience['id'], [
        ['op' => 'replace', 'path' => '/description', 'value' => 'Updated: All US-based customers'],
    ]);
    echo "Updated description: {$updatedAudience['description']}\n\n";

    // Clean up - delete the test audience
    echo "8. Deleting test audience...\n";
    $segmentation->deleteAudience($newAudience['id']);
    echo "Audience deleted successfully\n\n";
    */

    echo "Examples completed successfully!\n";

} catch (Adobe\Client\Exceptions\ApiException $e) {
    echo "API Error: " . $e->getMessage() . "\n";
    if ($response = $e->getResponse()) {
        echo "Status Code: " . $response->getStatusCode() . "\n";
        echo "Response Body: " . $response->getBody() . "\n";
    }
    exit(1);
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}

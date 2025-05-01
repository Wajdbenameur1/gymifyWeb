<?php
// Simple test script for IBM Watson Speech-to-Text API
error_reporting(E_ALL);
ini_set('display_errors', 1);

// API credentials
$apiKey = 'JR-LCWjnds3scsjrtCtn2wCNlyR2GWGAQq_hJ1D6Fhh-';
$serviceUrl = 'https://api.au-syd.speech-to-text.watson.cloud.ibm.com/instances/760398a7-3622-4319-83f8-415080a4ee25';

// Function to test connection
function testConnection($apiKey, $serviceUrl) {
    echo "<h3>Testing IBM Watson API Connection</h3>";
    
    // Initialize cURL
    $ch = curl_init();
    
    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $serviceUrl . '/v1/models');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  // Disable SSL verification
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Basic ' . base64_encode('apikey:' . $apiKey)
    ]);
    
    // Execute cURL request
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    
    // Print results
    echo "<p>HTTP Code: $httpCode</p>";
    
    if ($curlError) {
        echo "<p style='color: red;'>cURL Error: $curlError</p>";
    }
    
    if ($response === false) {
        echo "<p style='color: red;'>Failed to connect to IBM Watson API</p>";
    } else {
        echo "<p style='color: green;'>Successfully connected to IBM Watson API</p>";
        echo "<h4>Response:</h4>";
        echo "<pre>" . htmlspecialchars(substr($response, 0, 500)) . "...</pre>";
    }
    
    curl_close($ch);
}

// Function to transcribe audio (uses a preset file path)
function transcribeAudio($apiKey, $serviceUrl, $audioFilePath) {
    echo "<h3>Testing Transcription</h3>";
    
    if (!file_exists($audioFilePath)) {
        echo "<p style='color: red;'>File does not exist: $audioFilePath</p>";
        return;
    }
    
    echo "<p>File size: " . filesize($audioFilePath) . " bytes</p>";
    
    // Determine content type from file extension
    $extension = pathinfo($audioFilePath, PATHINFO_EXTENSION);
    $contentType = 'audio/wav';  // Default
    
    if ($extension === 'mp3') {
        $contentType = 'audio/mpeg';
    } elseif ($extension === 'ogg') {
        $contentType = 'audio/ogg';
    } elseif ($extension === 'flac') {
        $contentType = 'audio/flac';
    } elseif ($extension === 'webm') {
        $contentType = 'audio/webm';
    }
    
    echo "<p>Content type: $contentType</p>";
    
    // Initialize cURL
    $ch = curl_init();
    
    // Get file content
    $audioContent = file_get_contents($audioFilePath);
    
    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $serviceUrl . '/v1/recognize');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  // Disable SSL verification
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Basic ' . base64_encode('apikey:' . $apiKey),
        'Content-Type: ' . $contentType
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $audioContent);
    
    // Execute cURL request
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    
    // Print results
    echo "<p>HTTP Code: $httpCode</p>";
    
    if ($curlError) {
        echo "<p style='color: red;'>cURL Error: $curlError</p>";
    }
    
    if ($response === false) {
        echo "<p style='color: red;'>Failed to transcribe audio</p>";
    } else {
        echo "<h4>Response:</h4>";
        echo "<pre>" . htmlspecialchars(substr($response, 0, 1000)) . "...</pre>";
        
        // Parse response
        $result = json_decode($response, true);
        if (isset($result['results'])) {
            echo "<h4>Transcription:</h4>";
            $transcript = '';
            foreach ($result['results'] as $segment) {
                if (isset($segment['alternatives'][0]['transcript'])) {
                    $transcript .= $segment['alternatives'][0]['transcript'] . ' ';
                }
            }
            echo "<p><strong>" . htmlspecialchars(trim($transcript)) . "</strong></p>";
        } else {
            echo "<p style='color: red;'>No transcription results found in response</p>";
        }
    }
    
    curl_close($ch);
}

// HTML output
echo '<!DOCTYPE html>
<html>
<head>
    <title>IBM Watson API Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        pre { background: #f5f5f5; padding: 10px; overflow-x: auto; max-width: 100%; }
        .container { max-width: 800px; margin: 0 auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1>IBM Watson Speech-to-Text API Test</h1>';

// Test connection
testConnection($apiKey, $serviceUrl);

// Check if audio file was uploaded
if (isset($_FILES['audio_file']) && $_FILES['audio_file']['error'] == 0) {
    $tempFile = $_FILES['audio_file']['tmp_name'];
    $fileName = $_FILES['audio_file']['name'];
    
    echo "<p>Received file: $fileName</p>";
    
    // Transcribe uploaded audio
    transcribeAudio($apiKey, $serviceUrl, $tempFile);
}

// Upload form
echo '
        <h3>Upload Audio File</h3>
        <form method="post" enctype="multipart/form-data">
            <input type="file" name="audio_file" accept="audio/*">
            <button type="submit">Upload & Transcribe</button>
        </form>
    </div>
</body>
</html>'; 
<?php
/**
 * MKCG API Service
 * Handles all OpenAI API interactions for all generators
 */

class MKCG_API_Service {
    
    private $api_key;
    private $api_url = 'https://api.openai.com/v1/chat/completions';
    private $model = 'gpt-4o-mini';
    
    public function __construct() {
        $this->api_key = defined('OPENAI_API_KEY') ? OPENAI_API_KEY : '';
    }
    
    /**
     * Generate content using OpenAI API
     * 
     * @param string $prompt The user prompt
     * @param string $generator_type The type of generator (biography, offers, topics, questions)
     * @param array $options Additional options for the API call
     * @return array Success/error response
     */
    public function generate_content($prompt, $generator_type, $options = []) {
        if (!$this->api_key) {
            error_log('MKCG API Service: Missing OpenAI API key.');
            return [
                'success' => false,
                'message' => 'Server configuration error: Missing AI API key.'
            ];
        }
        
        $system_prompt = $this->get_system_prompt($generator_type);
        
        $body = json_encode([
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => $system_prompt],
                ['role' => 'user', 'content' => $prompt]
            ]
        ]);
        
        $response = wp_remote_post($this->api_url, [
            'method' => 'POST',
            'headers' => [
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type' => 'application/json',
            ],
            'body' => $body,
            'timeout' => 30,
        ]);
        
        if (is_wp_error($response)) {
            error_log('MKCG API Service: OpenAI API request failed: ' . $response->get_error_message());
            return [
                'success' => false,
                'message' => 'API request failed: ' . $response->get_error_message()
            ];
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        
        error_log('MKCG API Service: OpenAI API response code: ' . $response_code);
        
        if ($response_code !== 200) {
            error_log('MKCG API Service: OpenAI API error: ' . $response_body);
            return [
                'success' => false,
                'message' => 'OpenAI API returned error code: ' . $response_code,
                'api_response' => $response_body
            ];
        }
        
        $body = json_decode($response_body, true);
        
        if (empty($body['choices'][0]['message']['content'])) {
            error_log('MKCG API Service: No content in OpenAI API response');
            return [
                'success' => false,
                'message' => 'No content in API response.'
            ];
        }
        
        $content = $body['choices'][0]['message']['content'];
        $formatted_content = $this->format_response($content, $generator_type);
        
        return [
            'success' => true,
            'content' => $formatted_content,
            'raw_content' => $content
        ];
    }
    
    /**
     * Get system prompt based on generator type
     */
    private function get_system_prompt($generator_type) {
        switch ($generator_type) {
            case 'biography':
                return 'You are an AI that generates professional biographies for experts and professionals.';
                
            case 'offers':
                return 'You are an AI that generates compelling service offers and packages for experts.';
                
            case 'topics':
                return 'You are an AI that generates engaging interview topics that highlight expertise.';
                
            case 'questions':
                return 'You are an AI that generates engaging interview questions for podcasts.';
                
            default:
                return 'You are an AI assistant that generates professional content.';
        }
    }
    
    /**
     * Format API response based on generator type
     */
    private function format_response($content, $generator_type) {
        switch ($generator_type) {
            case 'biography':
                return $this->format_biography_response($content);
                
            case 'offers':
                return $this->format_offers_response($content);
                
            case 'topics':
                return $this->format_topics_response($content);
                
            case 'questions':
                return $this->format_questions_response($content);
                
            default:
                return $content;
        }
    }
    
    /**
     * Format biography response (extract different lengths)
     */
    private function format_biography_response($content) {
        // Look for different biography lengths in the response
        $biographies = [];
        
        // Try to extract short, medium, and long versions
        if (preg_match('/Short Bio.*?:(.*?)(?=Medium Bio|Long Bio|$)/s', $content, $matches)) {
            $biographies['short'] = trim($matches[1]);
        }
        
        if (preg_match('/Medium Bio.*?:(.*?)(?=Long Bio|Short Bio|$)/s', $content, $matches)) {
            $biographies['medium'] = trim($matches[1]);
        }
        
        if (preg_match('/Long Bio.*?:(.*?)(?=Short Bio|Medium Bio|$)/s', $content, $matches)) {
            $biographies['long'] = trim($matches[1]);
        }
        
        // If no specific formats found, return the full content
        if (empty($biographies)) {
            $biographies['full'] = $content;
        }
        
        return $biographies;
    }
    
    /**
     * Format offers response
     */
    private function format_offers_response($content) {
        // Parse offers from the response
        $offers = [];
        
        // Try to extract numbered offers
        if (preg_match_all('/\d+\.\s*[\'"]?(.*?)[\'"]?(?=\n\d+\.|\n\n|$)/s', $content, $matches)) {
            $offers = array_map('trim', $matches[1]);
        } else {
            // Fallback to line splitting
            $lines = explode("\n", $content);
            $offers = array_filter(array_map('trim', $lines));
        }
        
        return $offers;
    }
    
    /**
     * Format topics response (extracted from your Topics generator)
     */
    private function format_topics_response($content) {
        $topics = [];
        
        // Parse topics using regex (from your existing code)
        if (preg_match_all('/\d+\.\s*[\'"]?(.*?)[\'"]?(?=\n\d+\.|\n\n|$)/s', $content, $matches)) {
            $topics = array_map('trim', $matches[1]);
        } else {
            // Fallback to simple newline splitting
            $topics = array_filter(array_map(function($t) {
                return trim($t, " '\"");
            }, explode("\n", $content)));
        }
        
        return $topics;
    }
    
    /**
     * Format questions response (extracted from your Questions generator)
     */
    private function format_questions_response($content) {
        $questions = [];
        
        // Parse questions using regex (from your existing code)
        if (preg_match_all('/\d+\.\s*[\'"]?(.*?)[\'"]?(?=\n\d+\.|\n\n|$)/s', $content, $matches)) {
            $questions = array_map('trim', $matches[1]);
        } else {
            // Fallback to simple newline splitting
            $questions = array_filter(array_map(function($q) {
                return trim($q, " '\"");
            }, explode("\n", $content)));
        }
        
        return $questions;
    }
    
    /**
     * Test API connection
     */
    public function test_connection() {
        if (!$this->api_key) {
            return [
                'success' => false,
                'message' => 'No API key configured'
            ];
        }
        
        $test_response = $this->generate_content(
            'Respond with "API connection successful"',
            'test'
        );
        
        return $test_response;
    }
}
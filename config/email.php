<?php

return [
    // Email sender information
    'from_email' => 'sogniaw@gmail.com',
    'from_name' => 'AfricAvenir',
    
    // Login URL for members
    'login_url' => 'http://localhost/OG-afrcavenir/',
    
    // SMTP Configuration
    'use_smtp' => true,
    'smtp_host' => 'smtp.gmail.com', // Default placeholder
    'smtp_port' => 587,
    'smtp_username' => 'sogniaw@gmail.com', // To be filled by user
    'smtp_password' => '', // To be filled by user
    'smtp_encryption' => 'tls', // tls or ssl
    'smtp_debug' => 0, // 0 = off, 1 = client messages, 2 = client and server messages
];

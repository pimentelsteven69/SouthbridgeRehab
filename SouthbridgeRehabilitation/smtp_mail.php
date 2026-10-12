<?php
// Southbridge Rehabilitation - SMTP mail sender.
// Called by forgot_password.php:
// smtpSend($to, $toName, $subject, $body)

if (!function_exists('smtpSend')) {
    function smtpSend($to, $toName, $subject, $body)
    {
        $socket = null;

        try {
            $config = require __DIR__ . '/email_config.php';

            foreach (['host', 'port', 'encryption', 'username', 'password', 'from_email', 'from_name'] as $key) {
                if (!array_key_exists($key, $config)) {
                    return [false, "Missing '$key' setting in email_config.php."];
                }
            }

            $host = trim($config['host']);
            $port = (int)$config['port'];
            $encryption = strtolower(trim($config['encryption']));
            $username = trim($config['username']);
            $password = (string)$config['password'];
            $fromEmail = trim($config['from_email']);
            $fromName = (string)$config['from_name'];

            if (
                strpos($username, 'YOUR_') !== false ||
                strpos($password, 'YOUR_') !== false ||
                strpos($fromEmail, 'YOUR_') !== false
            ) {
                return [false, 'Email settings are not configured yet. Edit email_config.php.'];
            }

            if (!filter_var($to, FILTER_VALIDATE_EMAIL) || !filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
                return [false, 'The sender or recipient email address is invalid.'];
            }

            if (!in_array($encryption, ['tls', 'ssl', 'none'], true)) {
                return [false, 'Unsupported SMTP encryption. Use tls or ssl.'];
            }

            $remote = ($encryption === 'ssl' ? 'ssl://' : '') . $host . ':' . $port;
            $socket = @stream_socket_client($remote, $errno, $errstr, 15, STREAM_CLIENT_CONNECT);

            if (!$socket) {
                return [false, "Could not connect to SMTP server: $errstr ($errno)"];
            }

            stream_set_timeout($socket, 15);
            smtpExpect($socket, [220]);
            smtpCommand($socket, 'EHLO localhost', [250]);

            if ($encryption === 'tls') {
                smtpCommand($socket, 'STARTTLS', [220]);
                $crypto = @stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
                if ($crypto !== true) {
                    throw new RuntimeException('Could not establish a secure TLS connection to the SMTP server.');
                }
                smtpCommand($socket, 'EHLO localhost', [250]);
            }

            smtpCommand($socket, 'AUTH LOGIN', [334]);
            smtpCommand($socket, base64_encode($username), [334]);
            smtpCommand($socket, base64_encode($password), [235]);
            smtpCommand($socket, 'MAIL FROM:<' . $fromEmail . '>', [250]);
            smtpCommand($socket, 'RCPT TO:<' . $to . '>', [250, 251]);
            smtpCommand($socket, 'DATA', [354]);

            $headers = [
                'Date: ' . date(DATE_RFC2822),
                'From: ' . smtpMimeHeader($fromName) . ' <' . $fromEmail . '>',
                'To: ' . smtpMimeHeader((string)$toName) . ' <' . $to . '>',
                'Subject: ' . smtpMimeHeader((string)$subject),
                'MIME-Version: 1.0',
                'Content-Type: text/plain; charset=UTF-8',
                'Content-Transfer-Encoding: 8bit',
            ];

            $safeBody = str_replace(["\r\n", "\r"], "\n", (string)$body);
            $safeBody = preg_replace('/^\./m', '..', $safeBody);
            $message = implode("\r\n", $headers) . "\r\n\r\n" .
                       str_replace("\n", "\r\n", $safeBody) . "\r\n.";

            if (fwrite($socket, $message . "\r\n") === false) {
                throw new RuntimeException('Failed while sending the email message.');
            }
            smtpExpect($socket, [250]);

            @fwrite($socket, "QUIT\r\n");
            fclose($socket);
            $socket = null;

            return [true, ''];
        } catch (Throwable $e) {
            if (is_resource($socket)) {
                @fwrite($socket, "QUIT\r\n");
                @fclose($socket);
            }
            return [false, $e->getMessage()];
        }
    }

    function smtpCommand($socket, $command, $expectedCodes)
    {
        if (fwrite($socket, $command . "\r\n") === false) {
            throw new RuntimeException('Could not write to the SMTP server.');
        }
        smtpExpect($socket, $expectedCodes);
    }

    function smtpExpect($socket, $expectedCodes)
    {
        $response = '';
        while (($line = fgets($socket, 515)) !== false) {
            $response .= $line;
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }

        if ($response === '') {
            throw new RuntimeException('No response received from the SMTP server.');
        }

        $code = (int)substr($response, 0, 3);
        if (!in_array($code, $expectedCodes, true)) {
            $clean = trim(preg_replace('/\s+/', ' ', $response));
            throw new RuntimeException("SMTP server error ($code): $clean");
        }
        return $response;
    }

    function smtpMimeHeader($value)
    {
        $value = str_replace(["\r", "\n"], '', (string)$value);
        if (function_exists('mb_encode_mimeheader')) {
            return mb_encode_mimeheader($value, 'UTF-8', 'B');
        }
        if (preg_match('/[^\x20-\x7E]/', $value)) {
            return '=?UTF-8?B?' . base64_encode($value) . '?=';
        }
        return $value;
    }
}

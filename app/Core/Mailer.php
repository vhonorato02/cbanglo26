<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Cliente SMTP mínimo em PHP puro (sem dependências externas),
 * com suporte a STARTTLS (587), SSL implícito (465) e AUTH LOGIN.
 * Se SMTP não estiver configurado, o envio é silenciosamente pulado —
 * a aplicação nunca depende do e-mail para concluir uma inscrição.
 * Compatível com PHP 7.1+
 */
final class Mailer
{
    /** @var array */
    private $mail;

    public function __construct($mail) {
        $this->mail = $mail;
    }

    public static function fromConfig() {
        $config = require BASE_PATH . '/config/app.php';
        return new self($config['mail']);
    }

    public function isConfigured() {
        return (isset($this->mail['smtp_host']) ? $this->mail['smtp_host'] : '') !== ''
            && (isset($this->mail['from_address']) ? $this->mail['from_address'] : '') !== '';
    }

    /**
     * Envia um e-mail HTML. Retorna true em caso de sucesso.
     * Nunca lança exceção para o chamador — registra a falha e retorna false.
     */
    public function send($to, $subject, $html, $textAlt = '') {
        if (!$this->isConfigured()) {
            Logger::info('SMTP não configurado — e-mail não enviado.', ['subject' => $subject]);
            return false;
        }
        try {
            $this->smtpSend($to, $subject, $html, $textAlt);
            return true;
        } catch (\Throwable $e) {
            Logger::error('Falha no envio de e-mail: ' . $e->getMessage());
            return false;
        }
    }

    private function smtpSend($to, $subject, $html, $textAlt) {
        $host = $this->mail['smtp_host'];
        $port = (int) $this->mail['smtp_port'];
        $enc  = strtolower((string) (isset($this->mail['encryption']) ? $this->mail['encryption'] : ''));

        $remote = ($enc === 'ssl' ? 'ssl://' : 'tcp://') . $host . ':' . $port;
        $context = stream_context_create([
            'ssl' => ['SNI_enabled' => true, 'verify_peer' => true, 'verify_peer_name' => true],
        ]);
        $fp = @stream_socket_client($remote, $errno, $errstr, 20, STREAM_CLIENT_CONNECT, $context);
        if ($fp === false) {
            throw new \RuntimeException("Conexão SMTP falhou: {$errstr} ({$errno})");
        }
        stream_set_timeout($fp, 20);

        $read = function () use ($fp): string {
            $data = '';
            while (($line = fgets($fp, 515)) !== false) {
                $data .= $line;
                if (isset($line[3]) && $line[3] === ' ') {
                    break;
                }
            }
            return $data;
        };
        $cmd = function (string $command, array $expect) use ($fp, $read): string {
            fwrite($fp, $command . "\r\n");
            $response = $read();
            $code = (int) substr($response, 0, 3);
            if (!in_array($code, $expect, true)) {
                throw new \RuntimeException("SMTP '{$command}' retornou: " . trim(substr($response, 0, 120)));
            }
            return $response;
        };

        $greeting = $read();
        if ((int) substr($greeting, 0, 3) !== 220) {
            fclose($fp);
            throw new \RuntimeException('SMTP sem saudação 220.');
        }

        $hostname = parse_url(Env::get('APP_URL', 'localhost'), PHP_URL_HOST) ?: 'localhost';
        $cmd('EHLO ' . $hostname, [250]);

        if ($enc === 'tls') {
            $cmd('STARTTLS', [220]);
            if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                fclose($fp);
                throw new \RuntimeException('Falha ao iniciar TLS.');
            }
            $cmd('EHLO ' . $hostname, [250]);
        }

        if (($this->mail['smtp_user'] ?? '') !== '') {
            $cmd('AUTH LOGIN', [334]);
            $cmd(base64_encode($this->mail['smtp_user']), [334]);
            $cmd(base64_encode($this->mail['smtp_pass']), [235]);
        }

        $from = $this->mail['from_address'];
        $fromName = $this->mail['from_name'];
        $cmd('MAIL FROM:<' . $from . '>', [250]);
        $cmd('RCPT TO:<' . $to . '>', [250, 251]);
        $cmd('DATA', [354]);

        $boundary = 'cb' . bin2hex(random_bytes(12));
        if ($textAlt === '') {
            $textAlt = trim(strip_tags(preg_replace('/<br\s*\/?\s*>/i', "\n", $html) ?? ''));
        }
        $encodeHeader = function ($v) {
            return '=?UTF-8?B?' . base64_encode($v) . '?=';
        };

        $headers = [
            'Date: ' . date('r'),
            'From: ' . $encodeHeader($fromName) . " <{$from}>",
            'To: <' . $to . '>',
            'Subject: ' . $encodeHeader($subject),
            'MIME-Version: 1.0',
            'Message-ID: <' . bin2hex(random_bytes(16)) . '@' . $hostname . '>',
            'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
        ];
        $body = implode("\r\n", $headers) . "\r\n\r\n"
            . "--{$boundary}\r\n"
            . "Content-Type: text/plain; charset=utf-8\r\nContent-Transfer-Encoding: base64\r\n\r\n"
            . chunk_split(base64_encode($textAlt))
            . "--{$boundary}\r\n"
            . "Content-Type: text/html; charset=utf-8\r\nContent-Transfer-Encoding: base64\r\n\r\n"
            . chunk_split(base64_encode($html))
            . "--{$boundary}--\r\n";

        // Escapa linhas iniciadas por "." (dot-stuffing)
        $body = preg_replace('/^\./m', '..', $body);

        fwrite($fp, $body . "\r\n.\r\n");
        $response = $read();
        if ((int) substr($response, 0, 3) !== 250) {
            fclose($fp);
            throw new \RuntimeException('SMTP DATA não aceito: ' . trim(substr($response, 0, 120)));
        }
        fwrite($fp, "QUIT\r\n");
        fclose($fp);
    }
}

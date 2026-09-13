<?php
if (!function_exists('enviarEmailSmtp')) {
    function enviarEmailSmtp($destinatario, $assunto, $mensagemTexto, $replyToEmail = null, $replyToNome = null, $mensagemHtml = null) {
        $host = defined('SMTP_HOST') ? SMTP_HOST : (getenv('SMTP_HOST') ?: ($_ENV['SMTP_HOST'] ?? ''));
        $port = defined('SMTP_PORT') ? (int)SMTP_PORT : (int)(getenv('SMTP_PORT') ?: ($_ENV['SMTP_PORT'] ?? 587));
        $user = defined('SMTP_USER') ? SMTP_USER : (getenv('SMTP_USER') ?: ($_ENV['SMTP_USER'] ?? ''));
        $pass = defined('SMTP_PASS') ? SMTP_PASS : (getenv('SMTP_PASS') ?: ($_ENV['SMTP_PASS'] ?? ''));
        $fromEmail = defined('SMTP_FROM_EMAIL') ? SMTP_FROM_EMAIL : (getenv('SMTP_FROM_EMAIL') ?: ($_ENV['SMTP_FROM_EMAIL'] ?? ''));
        $fromNome = defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : (getenv('SMTP_FROM_NAME') ?: ($_ENV['SMTP_FROM_NAME'] ?? 'FixIt Assistencia'));

        if (empty($host) || empty($user) || empty($pass) || empty($fromEmail)) {
            $cabecalhos = "From: " . ($fromEmail ?: 'nao-responder@fixit.com') . "\r\n";
            if (!empty($replyToEmail)) {
                $cabecalhos .= "Reply-To: " . $replyToEmail . "\r\n";
            }
            if (!empty($mensagemHtml)) {
                $cabecalhos .= "MIME-Version: 1.0\r\n";
                $cabecalhos .= "Content-Type: text/html; charset=UTF-8\r\n";
                return @mail($destinatario, $assunto, $mensagemHtml, $cabecalhos);
            } else {
                $cabecalhos .= "Content-Type: text/plain; charset=UTF-8\r\n";
                return @mail($destinatario, $assunto, $mensagemTexto, $cabecalhos);
            }
        }

        $contexto = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ]);

        $timeout = 15;
        $prefixo = ($port == 465) ? 'ssl://' : 'tcp://';
        $socket = @stream_socket_client("{$prefixo}{$host}:{$port}", $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT, $contexto);
        if (!$socket) {
            error_log("SMTP Erro conexao em {$host}:{$port} - {$errstr} ({$errno})");
            return false;
        }

        stream_set_timeout($socket, $timeout);

        $lerResposta = function($socket) {
            $resposta = '';
            while ($linha = fgets($socket, 512)) {
                $resposta .= $linha;
                if (strlen($linha) >= 4 && substr($linha, 3, 1) === ' ') {
                    break;
                }
            }
            return $resposta;
        };

        $enviarComando = function($socket, $comando) {
            fputs($socket, $comando . "\r\n");
        };

        $resp = $lerResposta($socket);
        if (substr($resp, 0, 3) !== '220') {
            error_log("SMTP Erro handshake: " . trim($resp));
            fclose($socket);
            return false;
        }

        $enviarComando($socket, 'EHLO localhost');
        $resp = $lerResposta($socket);
        if (substr($resp, 0, 3) !== '250') {
            error_log("SMTP Erro EHLO: " . trim($resp));
            fclose($socket);
            return false;
        }

        if ($port != 465 && strpos($resp, 'STARTTLS') !== false) {
            $enviarComando($socket, 'STARTTLS');
            $resp = $lerResposta($socket);
            if (substr($resp, 0, 3) === '220') {
                if (!@stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    error_log("SMTP Erro: Falha TLS.");
                    fclose($socket);
                    return false;
                }

                $enviarComando($socket, 'EHLO localhost');
                $resp = $lerResposta($socket);
                if (substr($resp, 0, 3) !== '250') {
                    error_log("SMTP Erro EHLO pos-TLS: " . trim($resp));
                    fclose($socket);
                    return false;
                }
            }
        }

        $enviarComando($socket, 'AUTH LOGIN');
        $resp = $lerResposta($socket);
        if (substr($resp, 0, 3) !== '334') {
            error_log("SMTP Erro AUTH LOGIN: " . trim($resp));
            fclose($socket);
            return false;
        }

        $enviarComando($socket, base64_encode($user));
        $resp = $lerResposta($socket);
        if (substr($resp, 0, 3) !== '334') {
            error_log("SMTP Erro usuario: " . trim($resp));
            fclose($socket);
            return false;
        }

        $enviarComando($socket, base64_encode($pass));
        $resp = $lerResposta($socket);
        if (substr($resp, 0, 3) !== '235') {
            error_log("SMTP Erro senha: " . trim($resp));
            fclose($socket);
            return false;
        }

        $enviarComando($socket, "MAIL FROM:<{$fromEmail}>");
        $resp = $lerResposta($socket);
        if (substr($resp, 0, 3) !== '250') {
            error_log("SMTP Erro MAIL FROM: " . trim($resp));
            fclose($socket);
            return false;
        }

        $enviarComando($socket, "RCPT TO:<{$destinatario}>");
        $resp = $lerResposta($socket);
        if (substr($resp, 0, 3) !== '250') {
            error_log("SMTP Erro RCPT TO: " . trim($resp));
            fclose($socket);
            return false;
        }

        $enviarComando($socket, 'DATA');
        $resp = $lerResposta($socket);
        if (substr($resp, 0, 3) !== '354') {
            error_log("SMTP Erro DATA: " . trim($resp));
            fclose($socket);
            return false;
        }

        $codificarTexto = function($texto) {
            if (preg_match('/[^\x20-\x7E]/', $texto)) {
                return '=?UTF-8?B?' . base64_encode($texto) . '?=';
            }
            return $texto;
        };

        $assuntoFormatado = $codificarTexto($assunto);
        $fromNomeFormatado = $codificarTexto($fromNome);
        $fromCabecalho = !empty($fromNomeFormatado) ? "{$fromNomeFormatado} <{$fromEmail}>" : "<{$fromEmail}>";
        $dominioId = (strpos($fromEmail, '@') !== false) ? substr(strrchr($fromEmail, '@'), 1) : 'fixit.local';
        $messageId = sprintf("<%s.%s@%s>", bin2hex(random_bytes(8)), time(), $dominioId);

        $headers = [];
        $headers[] = "Date: " . date('r');
        $headers[] = "From: {$fromCabecalho}";
        $headers[] = "To: <{$destinatario}>";
        if (!empty($replyToEmail)) {
            $replyToNomeFormatado = !empty($replyToNome) ? $codificarTexto($replyToNome) : '';
            if (!empty($replyToNomeFormatado)) {
                $headers[] = "Reply-To: {$replyToNomeFormatado} <{$replyToEmail}>";
            } else {
                $headers[] = "Reply-To: <{$replyToEmail}>";
            }
        }
        $headers[] = "Subject: {$assuntoFormatado}";
        $headers[] = "Message-ID: {$messageId}";
        $headers[] = "X-Mailer: FixIt-Mailer-PHP";
        $headers[] = "MIME-Version: 1.0";

        $formatarLinhas = function($texto) {
            $norm = str_replace(["\r\n", "\r"], "\n", $texto);
            $linhas = explode("\n", $norm);
            $saida = [];
            foreach ($linhas as $l) {
                if (strpos($l, '.') === 0) {
                    $l = '.' . $l;
                }
                $saida[] = $l;
            }
            return implode("\r\n", $saida);
        };

        $boundary = '=_FixIt_' . md5(uniqid(microtime(), true));

        if (!empty($mensagemHtml)) {
            $headers[] = "Content-Type: multipart/alternative; boundary=\"{$boundary}\"";

            $corpo = "--{$boundary}\r\n";
            $corpo .= "Content-Type: text/plain; charset=UTF-8\r\n";
            $corpo .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
            $corpo .= $formatarLinhas($mensagemTexto) . "\r\n\r\n";

            $corpo .= "--{$boundary}\r\n";
            $corpo .= "Content-Type: text/html; charset=UTF-8\r\n";
            $corpo .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
            $corpo .= $formatarLinhas($mensagemHtml) . "\r\n\r\n";

            $corpo .= "--{$boundary}--\r\n";
        } else {
            $headers[] = "Content-Type: text/plain; charset=UTF-8";
            $headers[] = "Content-Transfer-Encoding: 8bit";
            $corpo = $formatarLinhas($mensagemTexto) . "\r\n";
        }

        $payload = implode("\r\n", $headers) . "\r\n\r\n" . $corpo . ".\r\n";
        fputs($socket, $payload);

        $resp = $lerResposta($socket);
        $sucesso = (substr($resp, 0, 3) === '250');
        if (!$sucesso) {
            error_log("SMTP Erro envio: " . trim($resp));
        }

        $enviarComando($socket, 'QUIT');
        fclose($socket);

        return $sucesso;
    }
}

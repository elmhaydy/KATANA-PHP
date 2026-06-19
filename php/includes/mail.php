<?php
// ===============================
// includes/mail.php
// Envoi email SMTP simple (MailHog)
// Host: 127.0.0.1  Port: 1025
// ===============================

function smtp_send_mail(
  string $to,
  string $subject,
  string $body,
  string $from = "no-reply@katana.test",
  string $fromName = "Mini e-Commerce"
): bool {

  $host = "127.0.0.1";
  $port = 1025;

  $fp = @fsockopen($host, $port, $errno, $errstr, 5);
  if (!$fp) return false;

  $read = function() use ($fp) {
    $data = "";
    while (!feof($fp)) {
      $line = fgets($fp, 515);
      if ($line === false) break;
      $data .= $line;
      // Réponse SMTP se termine généralement par "XYZ <message>\r\n" (avec espace après code)
      if (preg_match('/^\d{3}\s/', $line)) break;
    }
    return $data;
  };

  $write = function(string $cmd) use ($fp) {
    fwrite($fp, $cmd . "\r\n");
  };

  $expectOk = function(string $resp) {
    // Accepte 2xx ou 3xx
    return preg_match('/^[23]\d{2}/', trim($resp)) === 1;
  };

  $r = $read();
  if (!$expectOk($r)) { fclose($fp); return false; }

  $write("EHLO localhost");
  $r = $read();
  if (!$expectOk($r)) { fclose($fp); return false; }

  $write("MAIL FROM:<$from>");
  $r = $read();
  if (!$expectOk($r)) { fclose($fp); return false; }

  $write("RCPT TO:<$to>");
  $r = $read();
  if (!$expectOk($r)) { fclose($fp); return false; }

  $write("DATA");
  $r = $read();
  if (!preg_match('/^354/', trim($r))) { fclose($fp); return false; }

  $subjectSafe = str_replace(["\r", "\n"], "", $subject);

  $headers = [];
  $headers[] = "From: {$fromName} <{$from}>";
  $headers[] = "To: <{$to}>";
  $headers[] = "Subject: {$subjectSafe}";
  $headers[] = "MIME-Version: 1.0";
  $headers[] = "Content-Type: text/plain; charset=utf-8";

  $data = implode("\r\n", $headers) . "\r\n\r\n" . $body . "\r\n.";
  $write($data);

  $r = $read();
  if (!$expectOk($r)) { fclose($fp); return false; }

  $write("QUIT");
  fclose($fp);
  return true;
}

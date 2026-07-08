<?php
/**
 * E-mail de confirmação de inscrição (HTML compatível com clientes de e-mail).
 * $protocolo, $alunoNome, $serieNome, $escolaNome, $campanha, $dataProva, $horaProva, $comprovanteUrl
 */
$dataProvaBr = ($dataProva ?? '') !== '' ? data_br($dataProva) : '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head><meta charset="utf-8"><title><?= e($campanha) ?></title></head>
<body style="margin:0;padding:0;background-color:#f2f2f7;font-family:Arial,Helvetica,sans-serif;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f2f2f7;padding:24px 12px;">
<tr><td align="center">
<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background-color:#ffffff;border-radius:12px;overflow:hidden;">
  <tr>
    <td style="background-color:#221d4e;padding:28px 32px;text-align:center;">
      <p style="margin:0;color:#ffffff;font-size:22px;font-weight:bold;">Concurso <span style="color:#ffc20e;">de Bolsas</span></p>
    </td>
  </tr>
  <tr>
    <td style="padding:32px;">
      <h1 style="margin:0 0 16px;font-size:20px;color:#221d4e;">Inscrição recebida! 🎉</h1>
      <p style="margin:0 0 16px;font-size:15px;color:#333;line-height:1.6;">
        A inscrição de <strong><?= e($alunoNome) ?></strong> no <?= e($campanha) ?> foi registrada com sucesso.
      </p>
      <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f7f7fb;border-radius:8px;margin:0 0 20px;">
        <tr><td style="padding:20px;">
          <p style="margin:0 0 6px;font-size:13px;color:#666;">Número de protocolo</p>
          <p style="margin:0 0 14px;font-size:22px;font-weight:bold;color:#221d4e;letter-spacing:1px;"><?= e($protocolo) ?></p>
          <p style="margin:0 0 4px;font-size:14px;color:#333;"><strong>Série:</strong> <?= e($serieNome) ?></p>
          <p style="margin:0 0 4px;font-size:14px;color:#333;"><strong>Escola escolhida:</strong> <?= e($escolaNome) ?></p>
          <?php if ($dataProvaBr !== ''): ?>
          <p style="margin:0;font-size:14px;color:#333;"><strong>Prova:</strong> <?= e($dataProvaBr) ?><?= ($horaProva ?? '') !== '' ? ' às ' . e(substr($horaProva, 0, 5)) . 'h' : '' ?></p>
          <?php endif; ?>
        </td></tr>
      </table>
      <p style="margin:0 0 24px;font-size:15px;color:#333;line-height:1.6;">
        Guarde este protocolo. A escola escolhida entrará em contato pelo WhatsApp com as orientações da prova.
      </p>
      <p style="margin:0 0 8px;text-align:center;">
        <a href="<?= e($comprovanteUrl) ?>" style="display:inline-block;background-color:#ffc20e;color:#221d4e;font-size:15px;font-weight:bold;text-decoration:none;padding:14px 32px;border-radius:999px;">Ver comprovante</a>
      </p>
    </td>
  </tr>
  <tr>
    <td style="background-color:#f7f7fb;padding:20px 32px;text-align:center;">
      <p style="margin:0;font-size:12px;color:#888;line-height:1.6;">
        Anglo Pinda · Colégio Fênix · Colégio Drummond · Anglo Cruzeiro<br>
        Você recebeu este e-mail porque uma inscrição foi feita com este endereço.<br>
        Este é um e-mail automático — não é necessário responder.
      </p>
    </td>
  </tr>
</table>
</td></tr>
</table>
</body>
</html>

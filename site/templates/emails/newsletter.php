<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
  <html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="x-apple-disable-message-reformatting" />
    <!--[if !mso]><!-->
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <!--<![endif]-->
    <style type="text/css">
      * { text-size-adjust: 100%; -ms-text-size-adjust: 100%; -moz-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; }
      html { height: 100%; width: 100%; }
      body { height: 100% !important; margin: 0 !important; padding: 0 !important; width: 100% !important; mso-line-height-rule: exactly; }
      table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
    </style>
    <title><?= html($title) ?></title>
  </head>
  <body class="body" style="margin: 0; width: 100%;">
    <table role="presentation" width="100%" align="left" border="0" cellpadding="0" cellspacing="0" style="width: 100%; margin: 0;">
      <tr>
        <td align="left" width="100%" valign="top" style="color: #000000; font-family: Helvetica,Arial,sans-serif; font-size: 16px; line-height: 20px;">
          <div style="margin: 0 auto; width: 100%; max-width: 600px; padding: 25px">
            <img src="https://the-invisible-cities.com/assets/images/logo_email.png" alt="The Invisible Cities" border="0" style="margin: 0 0 25px; display: block; max-width: 220px;" />
            <?= $content ?>
            <p style="font-family: Helvetica,Arial,sans-serif; font-size: 12px; color: #999999; margin-top: 40px; border-top: 1px solid #eeeeee; padding-top: 16px;">
              You're receiving this because you subscribed to the The Invisible Cities newsletter.
              <a href="<?= html(url('newsletter')) ?>" style="color: #999999;">Unsubscribe</a>
            </p>
          </div>
        </td>
      </tr>
    </table>
  </body>
</html>

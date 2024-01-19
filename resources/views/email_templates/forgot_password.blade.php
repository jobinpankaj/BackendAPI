<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en-GB">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <title>Forgot Password Link</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>

  <style type="text/css">
    a[x-apple-data-detectors] {color: inherit !important;}
  </style>
</head>
<body style="margin: 0; padding: 0;">
  <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
    <tbody><tr>
      <td style="padding: 20px 0 30px 0;border-radius: 5px;">

        <table style="border-collapse: collapse;border: 1px solid #a5a5a5;" width="600" cellspacing="0" cellpadding="0" border="0" align="center">
          <tbody><tr>
            <td style="padding: 20px 0 20px 0;background-color: #9370DB;" align="center">
              <img src="https://dwcadmindev.iworklab.com/static/media/logo.8ebedae0d777ed358225d520cbe8b869.svg" alt="Buvons Local PRO" style="display: block;filter: invert(.5); max-width: 120px;" width="120" height="100">
              <!-- {{ asset('images/logo.svg') }} -->
            </td>
          </tr>
          <tr>
            <td style="padding: 40px 30px 40px 30px;background-color: #fff;">
              <table style="border-collapse: collapse;" width="100%" cellspacing="0" cellpadding="0" border="0">
                <tbody><tr>
                  <td style="color: #050030;font-family: Arial, sans-serif;text-align: start;">
                    <h1 style="font-size: 24px; margin: 0;">Hello <?php echo $details['name']; ?>,</h1>
                  </td>
                </tr>
                <tr>
                  <td style="color: #333; font-family: Arial, sans-serif; font-size: 16px; line-height: 24px; padding: 20px 0 30px 0;">
                    <?php echo $details['body']; ?>
                  </td>
                </tr>
                <tr>
                  <td style="color: #594f51; font-family: Arial, sans-serif; font-size: 16px; line-height: 24px; padding: 20px 0 30px 0;">
                    <p style="margin: 0; font-weight: bold;">Best Regards,</p>
                    <p style="margin: 0;">Buvons Local PRO Support Team</p>
                  </td>
                </tr>
              </tbody></table>
            </td>
          </tr>
          <tr>
            <td style="padding: 16px 30px;background-color: #9370DB;">
                <table style="border-collapse: collapse;" width="100%" cellspacing="0" cellpadding="0" border="0">
                  <tbody><tr>
                    <td style="color: #000; font-family: Arial, sans-serif; font-size: 16px; text-align: center;">
                      <p style="margin: 0;">Buvons Local PRO @2023</p>
                    </td>
                    
                  </tr>
                </tbody></table>
            </td>
          </tr>
        </tbody></table>

      </td>
    </tr>
  </tbody></table>
</body>
</html>


<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Foundation\Http\FormRequest;

const mail_template=<<<'EOT'
From: MAIL_TEMPLATE_FROM_ADDRESS
To: MAIL_TEMPLATE_TO_ADDRESS
Reply-To: MAIL_TEMPLATE_REPLY_ADDRESS
X-Mailer: ICE-V
Subject: MAIL_TEMPLATE_SUBJECT
Sender: MAIL_TEMPLATE_FROM_ADDRESS
MIME-Version: 1.0
Content-Type: multipart/mixed; boundary="1001"

This is message with multiparts in MIME format

--1001
Content-Type: multipart/related; boundary="1002"

--1002
Content-Type: multipart/alternative; boundary="1003"

--1003
Content-Type: text/plain; charset=windows-1251
Content-Transfer-Encoding: quoted-printable

This is HTML mail!

--1003
Content-Type: text/html; charset=windows-1251
Content-Transfer-Encoding: quoted-printable

<html>
<head>
</head>
<body>
<p>Hello</p>
MAIL_TEMPLATE_CONTENT
<img src=3D"cid:zzz.png">
</body>
</html>
--1003--

--1002
Content-Type: image/png; name="logo.png"
Content-Transfer-Encoding: base64
Content-Disposition: inline: filename="logo.png"
Content-ID: <zzz.png>

iVBORw0KGgoAAAANSUhEUgAAABkAAAAZCAYAAADE6YVjAAACXklEQVRIS71WzWsTQRR/M7vdfJiUxg/Qg5uKB0utYC8tFNGDsadeU5BWg0ehePTmQfwDPFh6Tz/8qCKiIIj2D+hBoahQRNGugohoSrPNxmR3xhnT3c7uTrIpmLxbZt97v/d77/dmgqALhrqAAW2DUErR0EPIVB1rX1xJbL/LQwkhRNspMhIku2BOUUJmWba+YEIWvIkwntm4lFpqBdYU5NiSlXVse50CjUdViwBVFVUd+DyV2JD5SkH0opmjQF5GJQ8xU3DOmE6tSBj7jzgD265/2SuA66+qPf1BRiEmerFstdMiTQGoOwDByfPWGYV0QizSB8KHTAhZdB1mTmkw+7bmI4VYxNpkCvpijdDba3/g9EEFCiuW54cxnhbF4APRi1slUUWv8ymwCcC5JyZUWdXcbo3GoXCixwe8XiIw/mzbO+OqMwq9GffAA+F7oM+XWcpduz4cA86G25ufDtxhrK6x38OHWK8Eyy6UgQb6ZlxOY3ePPJCTy3T/llX+JQY/n0jCYEYBHLFNrLiQTnoT6QPvJ9Fv/sELP36vcrRWsw3R+8wRBe5eSLYU2vcKhdFHZshH01T908XkVx+IjAl3uDkSgysDjZbJLP+iAqs/dgYmOEiZyGbCY8YOK3B/XM7mwyaB3NPdgYtFSGfCHYLqcoMm+lWYO+uTPnwzCYw9lgM0VRdPGNwTrq6rQxoowuC5lG+sVuHBx3rTFrbckwab8MZzEBWzDWcCJxGXe+TGc5Cu3F3/2Cya56lDXrXUruQjavcWdmM7/p6IRXb0ZQx2o6Nv/F7nIvOP/CPxP0D+Avcf/Bpt5AEbAAAAAElFTkSuQmCC

--1002--

--1001
Content-Type: text/plain; name="txt.txt"
Content-Transfer-Encoding: base64
Content-Disposition: attachment; filename="txt.txt"

MAIL_TEMPLATE_ATTACHMENT_CONTENT

--1001--

EOT;

const png = <<<'EOT'
iVBORw0KGgoAAAANSUhEUgAAABkAAAAZCAYAAADE6YVjAAACXklEQVRIS71WzWsTQRR/M7vdfJiUxg/Qg5uKB0utYC8tFNGDsadeU5BWg0ehePTmQfwDPFh6Tz/8qCKiIIj2D+hBoahQRNGugohoSrPNxmR3xhnT3c7uTrIpmLxbZt97v/d77/dmgqALhrqAAW2DUErR0EPIVB1rX1xJbL/LQwkhRNspMhIku2BOUUJmWba+YEIWvIkwntm4lFpqBdYU5NiSlXVse50CjUdViwBVFVUd+DyV2JD5SkH0opmjQF5GJQ8xU3DOmE6tSBj7jzgD265/2SuA66+qPf1BRiEmerFstdMiTQGoOwDByfPWGYV0QizSB8KHTAhZdB1mTmkw+7bmI4VYxNpkCvpijdDba3/g9EEFCiuW54cxnhbF4APRi1slUUWv8ymwCcC5JyZUWdXcbo3GoXCixwe8XiIw/mzbO+OqMwq9GffAA+F7oM+XWcpduz4cA86G25ufDtxhrK6x38OHWK8Eyy6UgQb6ZlxOY3ePPJCTy3T/llX+JQY/n0jCYEYBHLFNrLiQTnoT6QPvJ9Fv/sELP36vcrRWsw3R+8wRBe5eSLYU2vcKhdFHZshH01T908XkVx+IjAl3uDkSgysDjZbJLP+iAqs/dgYmOEiZyGbCY8YOK3B/XM7mwyaB3NPdgYtFSGfCHYLqcoMm+lWYO+uTPnwzCYw9lgM0VRdPGNwTrq6rQxoowuC5lG+sVuHBx3rTFrbckwab8MZzEBWzDWcCJxGXe+TGc5Cu3F3/2Cya56lDXrXUruQjavcWdmM7/p6IRXb0ZQx2o6Nv/F7nIvOP/CPxP0D+Avcf/Bpt5AEbAAAAAElFTkSuQmCC
EOT;

class MailController extends Controller
{
    
    //Отправка файла из строки на клиент
    public function sendFileLikeString() {
        //$contents = 'Get the contents from somewhere';
        $contents = base64_decode(png);
        $filename = 'ico.png';
        return response()->streamDownload(function () use ($contents) {
            echo $contents;
        }, $filename);
        //return "Done!";
    }

    public function send(Request $request) {
        $content=json_decode($request->getContent(), true);
        $mail = mail_template;
        $mail = str_replace("MAIL_TEMPLATE_FROM_ADDRESS", $content['from'], $mail);
        $mail = str_replace("MAIL_TEMPLATE_TO_ADDRESS", $content['to'], $mail);
        $mail = str_replace("MAIL_TEMPLATE_REPLY_ADDRESS", $content['reply'], $mail);
        $mail = str_replace("MAIL_TEMPLATE_CONTENT", $content['content'], $mail);
        $mail = str_replace("MAIL_TEMPLATE_SUBJECT", $content['subject'], $mail);
        $mail = str_replace("MAIL_TEMPLATE_ATTACHMENT_CONTENT", $content['attach'], $mail);
        //setlocale(LC_CTYPE, 'POSIX');
        //mb_convert_encoding($mail,"Windows-1251");
        $mail = iconv("UTF-8", "Windows-1251", $mail);
        $filename = 'test.eml';
        return response()->streamDownload(function () use ($mail) {
            echo $mail;
        }, $filename);
    }
}

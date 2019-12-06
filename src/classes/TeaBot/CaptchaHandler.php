<?php

namespace TeaBot;

/**
 * @author Ammar Faizi <ammarfaizi2@gmail.com> https://www.facebook.com/ammarfaizi2
 * @license MIT
 * @package \TeaBot
 * @version 6.2.0
 */
final class CaptchaHandler
{
    /**
     * @var \TeaBot\Data
     */
    public $data;

    /**
     * @var string
     */
    public $type;

    /**
     * @param \TeaBot\Data
     *
     * Constructor.
     */
    public function __construct(Data $data, string $type)
    {
        $this->data = $data;
        $this->type = $type;
    }

    /**
     * @return void
     */
    public function run(): void
    {
        switch ($this->type) {
            case "calculus":
                $this->calculusCaptcha();
                break;
            
            default:
                break;
        }
    }

    /**
     * @return void
     */
    private function calculusCaptcha()
    {
        $extra = rand(0, 10);
        $latex = "\int_{0}^{\infty} t^{".$extra."} e^{-t} dt";

        $msg = "<b>Please solve this problem in 5 minutes to make sure you are a human!</b>\nReply your answer to this message!\n\n".
            "Integrate the following expression:\n<code>".htmlspecialchars($latex, ENT_QUOTES, "UTF-8")."</code>";
        $photo = "https://api.teainside.org/latex_x.php?d=400&exp=".urlencode($latex);

        Exe::sendPhoto(
            [
                "chat_id" => $this->data["chat_id"],
                "reply_to_message_id" => $this->data["msg_id"],
                "caption" => $msg,
                "photo" => $photo,
                "parse_mode" => "HTML"
            ]
        );

    }
}

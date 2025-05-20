<?php
namespace Utils;

class TwigExtension extends \Twig_Extension
{
    private $_flash;
    public function __construct($flash)
    {
        $this->_flash = $flash;
    }

    public function getName()
    {
        return 'Utils';
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('flash_messages', [$this, 'flashMessages'], ['is_safe' => ['html']]),
        ];
    }

    public function flashMessages($messageKey = null, $class = null, $appName = 'default')
    {
        $messages = $this->_flash->getMessages();
        $button = '<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>';
        $html = '<div class="alert %s alert-dismissable fade in %s">%s %s</div>';
        $fadeout = '';
        $message = '';
        $alert = '';

        if(count($messages) > 0){
            if ($messageKey) {
                if (isset($messages[$messageKey])) {
                    $message = implode(", ", $messages[$messageKey]);
                    $alert = $class;
                }
            } else {
                if (isset($messages['message'])) {
                    $message = implode(", ", $messages['message']);
                    $alert = 'alert-success';
                    $fadeout = 'autofadeout';
                }
                if (isset($messages['error'])) {
                    $message = implode(", ", $messages['error']);
                    $alert = 'alert-danger';
                }
            }
            $html = sprintf($html,$alert,$fadeout,$message, $button);
        }else{
            $html = '';
        }

        return $html;
    }
}

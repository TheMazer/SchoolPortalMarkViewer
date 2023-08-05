<?php
namespace app\forms;

use Exception;
use std, gui, framework, app;


class Settings extends AbstractForm
{

    /**
     * @event close 
     */
    function doClose(UXWindowEvent $e = null)
    {    
        app()->shutdown();
    }

    /**
     * @event link.action 
     */
    function doLinkAction(UXEvent $e = null)
    {
        $this->cfg->set('masterToken', $this->masterTokenEdit->text);
        $this->cfg->set('schoolID', $this->schoolIDEdit->text);
        $this->cfg->load();
        $this->loadForm('MainForm', false, true);
    }

    /**
     * @event show 
     */
    function doShow(UXWindowEvent $e = null)
    {    
        $this->masterTokenEdit->text = $this->cfg->get('masterToken');
        $this->schoolIDEdit->text = $this->cfg->get('schoolID');
    }

    /**
     * @event refreshTokenBtn.action 
     */
    function doRefreshTokenBtnAction(UXEvent $e = null)
    {    
        try {
            $token = file_get_contents('https://pastebin.com/raw/pgMQT3pg');
            $this->masterTokenEdit->text = $token;
        } catch (Exception $e) {
            $this->toast('Не удалось загрузить токен, проверьте подключение к интернету');
        }
    }

    /**
     * @event resetIDBtn.action 
     */
    function doResetIDBtnAction(UXEvent $e = null)
    {    
        $this->schoolIDEdit->text = '2000000000073';
    }











}

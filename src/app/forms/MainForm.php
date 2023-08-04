<?php
namespace app\forms;

use Exception;
use std, gui, framework, app;


class MainForm extends AbstractForm
{

    /**
     * @event close 
     */
    function doClose(UXWindowEvent $e = null)
    {    
        app()->shutdown();
    }

    /**
     * @event panelAlt.mouseEnter 
     */
    function doPanelAltMouseEnter(UXMouseEvent $e = null)
    {    
        Animation::fadeTo($e->sender, 100, 0.7);
    }

    /**
     * @event panelAlt.mouseExit 
     */
    function doPanelAltMouseExit(UXMouseEvent $e = null)
    {    
        Animation::fadeTo($e->sender, 100, 1);
    }

    /**
     * @event panel3.mouseEnter 
     */
    function doPanel3MouseEnter(UXMouseEvent $e = null)
    {
        Animation::fadeTo($e->sender, 100, 0.7);
    }

    /**
     * @event panel3.mouseExit 
     */
    function doPanel3MouseExit(UXMouseEvent $e = null)
    {
        Animation::fadeTo($e->sender, 100, 1);
    }

    /**
     * @event panelAlt.click-Left 
     */
    function doPanelAltClickLeft(UXMouseEvent $e = null)
    {    
        $this->loadForm('PersonTool', false, true);
    }

    /**
     * @event panel3.click-Left 
     */
    function doPanel3ClickLeft(UXMouseEvent $e = null)
    {    
        $this->loadForm('GroupTool', false, true);
    }

    /**
     * @event panel4.mouseEnter 
     */
    function doPanel4MouseEnter(UXMouseEvent $e = null)
    {
        Animation::fadeTo($e->sender, 100, 0.7);
    }

    /**
     * @event panel4.mouseExit 
     */
    function doPanel4MouseExit(UXMouseEvent $e = null)
    {
        Animation::fadeTo($e->sender, 100, 1);
    }

    /**
     * @event panel4.click-Left 
     */
    function doPanel4ClickLeft(UXMouseEvent $e = null)
    {
        $this->loadForm('Settings', false, true);
    }

}

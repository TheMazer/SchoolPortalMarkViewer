<?php
namespace app\forms;

use Exception;
use std, gui, framework, app;


class PersonTool extends AbstractForm
{

    /**
     * @event getMarksPeriod.action 
     */
    function doGetMarksPeriodAction(UXEvent $e = null)
    {
        global $id;
        
        if ($this->methodSelect->selectedIndex == 0) {
            $url = 'http://api.school.mosreg.ru/v2/persons/'. $id .'/schools/'. $this->schoolID->text .'/marks/'. $this->periodFrom->value .'/'. $this->periodTo->value;
        } else {
            $url = 'https://api.school.mosreg.ru/v2/persons/'. $id .'/edu-groups/'. $this->groupID->text .'/marks/'. $this->periodFrom->value .'/'. $this->periodTo->value;
        }
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['accept: application/json', 'Access-Token: '. $this->masterTokenEdit->text]);
        
        
        $result = json_decode(curl_exec($ch), true);
        
        
        var_dump($url);
        var_dump($result);
        
        foreach ($result as $markData) {
            $mark = intval($markData['value']);
            
            if ($mark == 5) {
                $mFive += 1;
            } elseif ($mark == 4) {
                $mFour += 1;
            } elseif ($mark == 3) {
                $mThree += 1;
            } elseif ($mark == 2) {
                $mTwo += 1;
            }
        }
        
        $marksResultWindow = app()->showNewForm('MarksCount');
        $marksResultWindow->showMarks($mFive, $mFour, $mThree, $mTwo, $this->periodFrom->value, $this->periodTo->value, $this->personChoose->value);
        
        if ($result == '') {
            $marksResultWindow->toast('Возможно, не удалось получить информацию', 1000);
        }
    }

    /**
     * @event show 
     */
    function doShow(UXWindowEvent $e = null)
    {    
        $this->personChoose->items->setAll($this->persons->sections());
        $this->personChoose->selectedIndex = 0;
        $this->masterTokenEdit->text = $this->cfg->get('masterToken');
    }

    /**
     * @event personChoose.action 
     */
    function doPersonChooseAction(UXEvent $e = null)
    {    
        global $id;
        //// $token = $this->persons->get('token', $this->personChoose->value);
        $id = $this->persons->get('id', $this->personChoose->value);
        //// $group = $this->persons->get('group', $this->personChoose->value);
        
        if ($this->usePersonTokensCheckbox->selected) {
            $this->masterTokenEdit->text = $this->persons->get('token', $this->personChoose->value);
        }
        
        $this->groupID->text = $this->persons->get('group', $this->personChoose->value);
        
        
        $this->personLabel3->text = $id;
        $this->personLabel3->tooltipText = $this->personLabel3->text;
    }

    /**
     * @event close 
     */
    function doClose(UXWindowEvent $e = null)
    {    
        app()->shutdown();
    }

    /**
     * @event openPersonsIni.action 
     */
    function doOpenPersonsIniAction(UXEvent $e = null)
    {    
        if (fs::exists('persons.ini')) {
            open('persons.ini');
        } else {
            fs::makeFile('persons.ini');
            open('persons.ini');
        }
    }

    /**
     * @event methodSelect.action 
     */
    function doMethodSelectAction(UXEvent $e = null)
    {    
        if ($this->methodSelect->selectedIndex == 0) {
            $this->schoolIDLabel->enabled = true;
            $this->schoolID->enabled = true;
            $this->groupIDLabel->enabled = false;
            $this->groupID->enabled = false;
        } else {
            $this->schoolIDLabel->enabled = false;
            $this->schoolID->enabled = false;
            $this->groupIDLabel->enabled = true;
            $this->groupID->enabled = true;
        }
    }

    /**
     * @event usePersonTokensCheckbox.click 
     */
    function doUsePersonTokensCheckboxClick(UXMouseEvent $e = null)
    {    
        if ($this->usePersonTokensCheckbox->selected) {
            $this->masterTokenEdit->text = $this->persons->get('token', $this->personChoose->value);
        }
    }

    /**
     * @event link.action 
     */
    function doLinkAction(UXEvent $e = null)
    {    
        $this->loadForm('MainForm', false, true);
    }



}

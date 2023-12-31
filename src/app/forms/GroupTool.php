<?php
namespace app\forms;

use Exception;
use std, gui, framework, app;


class GroupTool extends AbstractForm
{

    /**
     * @event getMarksPeriod.action 
     */
    function doGetMarksPeriodAction(UXEvent $e = null)
    {
        $this->listView->items->clear();
        
        //if ($this->groupChoose->selected == 'Слёт') {
        //    $groupIDs = [
        //    '2000003226267', // Klokan
        //    '2000002842717', '2000001054739', '2000002842708', '2000002842720', '2000002842705', // Korolev, Kostenko, Mazepa, Pikulyk, Ryzhenkov
        //    '2000003260570', // Pablo
        //    '2000002008483', '2000003785928', '2000002032518', '2000002158829' // Karpenko, Matyushov, Ponomarev, Suslik
        //    ];
        //}
        
        $groupIDs = $this->groups->section($this->groupChoose->selected);
        
        $masterToken = $this->cfg->get('masterToken');
        $schoolID = $this->cfg->get('schoolID');
        //$eduGroupID = $this->groupID->text;
        $eduGroupIDs = [
            '2000003226267' => '1953236298024326221', // Klokan
            '2000002842717' => '1953236130520601671', // Korolev
            '2000001054739' => '1953236130520601671', // Kostenko
            '2000002842708' => '1953236130520601671', // Mazepa
            '2000002842720' => '1953236130520601671', // Pikulyk
            '2000002842705' => '1953236130520601671', // Ryzhenkov
            '2000003260570' => '1953235980196746305', // Pablo
            '2000002008483' => '1953235842757792830', // Karpenko
            '2000003785928' => '1953235842757792830', // Matyushov
            '2000002032518' => '1953235842757792830', // Ponomarev
            '2000002158829' => '1953235842757792830'  // Suslik
        ];
        $method = $this->methodSelect->selectedIndex;
        $showAbsent = $this->showAbsent->selected;
        
        try {
            foreach (explode("\n", file_get_contents('https://pastebin.com/raw/9yE5dHPh')) as $line) {
                $bypasslist[] = explode(':', $line); // [ ['id', 'mF', 'mF', 'mT', 'mT', 'notListedIfAbsent'], [...] ]
            }
        } catch (Exception $e) {
            Logger::warn("Can't get bypass list!");
            $bypasslist = [];
        }
        
        $thread = new Thread(function() use ($groupIDs, $masterToken, $schoolID, $eduGroupIDs, $method, $showAbsent, $bypasslist) {
        
            $lists = [];
        
            foreach ($groupIDs as $personID) {
            $ch = curl_init('https://api.school.mosreg.ru/v2/persons/'. $personID);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['accept: application/json', 'Access-Token: '. $masterToken]);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
            
            $badName = true;
            $badAttempts = 0;
            while ($badName and $badAttempts < 7) {
                var_dump('Getting name for '. $personID. '...');
                $result = json_decode(curl_exec($ch), true);
                if ($result) { $badName = false; } else { Logger::warn("Can't get name for ". $personID. ", retrying..."); $badAttempts++; }
            }
            if ($badAttempts < 7) { $name = $result['shortName']. '.'; } else { $name = '–'; }
            
            $ch = curl_init('https://api.school.mosreg.ru/v2/persons/'. $personID. '/edu-groups');
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['accept: application/json', 'Access-Token: '. $masterToken]);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
            
            $badClass = true;
            $badAttempts = 0;
            while ($badClass and $badAttempts < 7) {
                var_dump('Getting class for '. $personID. '...');
                $result = json_decode(curl_exec($ch), true);
                if ($result) { $badClass = false; } else { Logger::warn("Can't get class for ". $personID. ", retrying..."); $badAttempts++; }
            }
            if ($badAttempts < 7) { $class = $result[1]['name']; } else { $class = '–'; }
            
            if ($method == 0) {
                $url = 'http://api.school.mosreg.ru/v2/persons/'. $personID. '/schools/'. $schoolID .'/marks/'.
                $this->periodFrom->value .'/'. $this->periodTo->value;
            } else {
                $url = 'https://api.school.mosreg.ru/v2/persons/'. $personID. '/edu-groups/'. $eduGroupIDs[$personID] .'/marks/'.
                $this->periodFrom->value .'/'. $this->periodTo->value;
            }
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['accept: application/json', 'Access-Token: '. $masterToken]);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
            
            $badMarks = true;
            $badAttempts = 0;
            while ($badMarks and $badAttempts < 7) {
                var_dump('Getting marks for '. $personID. ': '. $url. "\n");
                $result = json_decode(curl_exec($ch), true);
                if ($result) { $badMarks = false; } else { Logger::warn("Can't get marks for ". $personID. ", retrying..."); $badAttempts++; }
            }
            if ($badAttempts < 7) {
            
                $mFive = 0; $mFour = 0; $mThree = 0; $mTwo = 0;
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
                
                foreach ($bypasslist as $rule) { // , ['id', 'mF', 'mF', 'mT', 'mT', 'notListedIfAbsent'], [...],
                    //Logger::info($personID. ' and '. $rule[0]);
                    if ($personID == $rule[0]) {
                        $mFive += $rule[1];
                        $mFour += $rule[2];
                        $mThree += $rule[3];
                        $mTwo += $rule[4];
                        if ($mFive < 0) { $mFive = 0; }
                        if ($mFour < 0) { $mFour = 0; }
                        if ($mThree < 0) { $mThree = 0; }
                        if ($mTwo < 0) { $mTwo = 0; }
                    }
                }
            
            } else {
                // В этом блоке просто было: $mFive = $mFour = $mThree = $mTwo = '–';
                $ch = curl_init('https://api.school.mosreg.ru/v2/persons/'. $personID. '/lesson-log-entries?startDate='.
                $this->periodFrom->value. '&endDate='. $this->periodTo->value);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['accept: application/json', 'Access-Token: '. $masterToken]);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
                
                $badStatus = true;
                $badAttempts = 0;
                while ($badStatus and $badAttempts < 3) {
                    var_dump('Getting status for '. $personID. '...');
                    $result = json_decode(curl_exec($ch), true);
                    if ($result) { $badStatus = false; } else { Logger::warn("Can't get status for ". $personID. ", retrying..."); $badAttempts++; }
                }
                if ($badAttempts < 3) {
                    
                    $skipped = false;
                    foreach ($result['logEntries'] as $status) {
                        if ($status['status'] == 'Pass' or $status['status'] == 'Ill') {
                            $skipped = true;
                        }
                    }
                    
                    if ($skipped) {
                        $mFive = $mFour = $mThree = $mTwo = '–'; // In case of ill or pass
                    } else {
                        $mFive = $mFour = $mThree = $mTwo = 0;
                    }
                    
                } else {
                    $mFive = $mFour = $mThree = $mTwo = '–';
                }
            
            }
            
            
            $lists[] = [$name, $class, $mFive, $mFour, $mThree, $mTwo];
            
            //Logger::info('Sleeping...');
            //sleep(3); v zhopu
            
            }
    
            uiLater(function () use ($lists, $groupIDs, $showAbsent) {
                $badList = 0;
            
                foreach ($lists as $listItem) {
                    if (!(strval($listItem[0]) == '–' or strval($listItem[1]) == '–' or ( strval($listItem[2]) == '–' and
                        strval($listItem[3]) == '–' and strval($listItem[4]) == '–' and strval($listItem[5]) == '–')) or $showAbsent) {
                            $this->markListAdd($listItem[0], $listItem[1], $listItem[2], $listItem[3], $listItem[4], $listItem[5]);
                    }
                    
                    if (strval($listItem[0]) == '–' or strval($listItem[1]) == '–' or ( strval($listItem[2]) == '–' and
                        strval($listItem[3]) == '–' and strval($listItem[4]) == '–' and strval($listItem[5]) == '–' )) { $badList++; }
                }
                
                if ($badList >= count($groupIDs)) {
                    $this->toast("Не удалось загрузить данные учеников!\n".
                    "Виной этому могут быть следующие причины:\n".
                    "1) Отсутствует интернет-соединение\n".
                    "2) На школьном портале ведуться тех. работы");
                }
            
                $this->hidePreloader();
            });
            
        });
        
        $this->showPreloader("Загрузка, пожалуйста подождите...\nЭто зависит от скорости интернета"); // Перед потоком покажем индикатор
        $thread->start(); // Запуск потока
        
    }
    
    function setImage($img) {
        $View = new UXImageArea();
        $View->size = [32, 32];
        $View->centered = true;
        $View->proportional = true;
        $View->stretch = true;
        $View->smartStretch = true;
        $View->image = new UXImage('res://.data/img/markIcons/'. $img. '.png');
        return $View;
    }
    
    function markListAdd($name, $class, $mFive, $mFour, $mThree, $mTwo) {
    
        if ($mTwo > 0) {
            $image = 'bad';
            //$bg = '#FFB7B7';
        } elseif ($mThree > 0) {
            $image = 'normal';
            //$bg = '#FCD3A2';
        } else {
            $image = 'good';
        }
        //if (!$this->cfg->get('sellHighlightByMark')) { $bg = null; }
        
        $this->listView->items->add([$name, $class, self::setImage($image), $mFive, $mFour, $mThree, $mTwo]);
    }

    /**
     * @event show 
     */
    function doShow(UXWindowEvent $e = null)
    {    
        $this->cfg->load();
        $this->groupChoose->items->setAll($this->groups->sections());
        $this->groupChoose->selectedIndex = 0;
        
        $this->listView->items->clear();
        $this->listView->fixedCellSize = 60;
        
        $this->listView->setCellFactory(function(UXListCell $cell, $item) {
            if ($item) {              
                $titleName = new UXLabel($item[0]);
                $titleName->style = '-fx-font-weight: bold; -fx-font-size: 16;'; 
                $titleName->width = 128;
             
                $titleDescription = new UXLabel($item[1]);
                $titleDescription->style = '-fx-text-fill: gray; -fx-font-size: 14;';
                if (strval($item[3]) == '–' and strval($item[4]) == '–' and strval($item[5]) == '–' and strval($item[6]) == '–') {
                    $titleDescription->text = $titleDescription->text. ', отсутствовал';
                }
             
                $title = new UXVBox([$titleName, $titleDescription]);
                $title->spacing = -5;
                
                // FIVE CONTAINER
                $m = new UXLabel('5');
                $m->classesString = 'mG';
                $m->size = [32, 32];
                $m->alignment = 'CENTER';
                $m->textColor = '#fff';
                $m->font->bold = true;
                $m->font->size = 16;
                
                $fiveAmount = new UXLabel($item[3]);
                $fiveAmount->size = [32, 32];
                $fiveAmount->alignment = 'CENTER';
                $fiveAmount->font->size = 16;
                
                $fiveContainer = new UXHBox([$fiveAmount, $m]);
                $fiveContainer->spacing = 0; // Between logo and text
                $fiveContainer->padding = 0; // Sell insets
                $fiveContainer->alignment = 'CENTER_LEFT';
                if ($item[3] == 0 or $item[3] == '–') {
                    $fiveContainer->opacity = 0.3;
                }
                
                // FOUR CONTAINER
                $m = new UXLabel('4');
                $m->classesString = 'mG';
                $m->size = [32, 32];
                $m->alignment = 'CENTER';
                $m->textColor = '#fff';
                $m->font->bold = true;
                $m->font->size = 16;
                
                $fourAmount = new UXLabel($item[4]);
                $fourAmount->size = [32, 32];
                $fourAmount->alignment = 'CENTER';
                $fourAmount->font->size = 16;
                
                $fourContainer = new UXHBox([$fourAmount, $m]);
                $fourContainer->spacing = 0; // Between logo and text
                $fourContainer->padding = 0; // Sell insets
                $fourContainer->alignment = 'CENTER_LEFT';
                if ($item[4] == 0 or $item[4] == '–') {
                    $fourContainer->opacity = 0.3;
                }
                
                // THREE CONTAINER
                $m = new UXLabel('3');
                $m->classesString = 'mO';
                $m->size = [32, 32];
                $m->alignment = 'CENTER';
                $m->textColor = '#fff';
                $m->font->bold = true;
                $m->font->size = 16;
                
                $threeAmount = new UXLabel($item[5]);
                $threeAmount->size = [32, 32];
                $threeAmount->alignment = 'CENTER';
                $threeAmount->font->size = 16;
                
                $threeContainer = new UXHBox([$threeAmount, $m]);
                $threeContainer->spacing = 0; // Between logo and text
                $threeContainer->padding = 0; // Sell insets
                $threeContainer->alignment = 'CENTER_LEFT';
                if ($item[5] == 0 or $item[5] == '–') {
                    $threeContainer->opacity = 0.3;
                }
                
                // TWO CONTAINER
                $m = new UXLabel('2');
                $m->classesString = 'mR';
                $m->size = [32, 32];
                $m->alignment = 'CENTER';
                $m->textColor = '#fff';
                $m->font->bold = true;
                $m->font->size = 16;
                
                $twoAmount = new UXLabel($item[6]);
                $twoAmount->size = [32, 32];
                $twoAmount->alignment = 'CENTER';
                $twoAmount->font->size = 16;
                
                $twoContainer = new UXHBox([$twoAmount, $m]);
                $twoContainer->spacing = 0; // Between logo and text
                $twoContainer->padding = 0; // Sell insets
                $twoContainer->alignment = 'CENTER_LEFT';
                if ($item[6] == 0 or $item[6] == '–') {
                    $twoContainer->opacity = 0.3;
                }
                
                // POINTS CONTAINER
                $add = new UXLabel( $item[3]*1 );
                $add->classesString = 'greyBg';
                $add->size = [32, 20];
                $add->alignment = 'CENTER';
                $add->textColor = '#9ba82e';
                $add->font->bold = true;
                $add->font->size = 14;
                if ($add->text == '0') {
                    $add->opacity = 0.3;
                }
                
                $remove = new UXLabel( $item[6]*(-5) );
                $remove->classesString = 'greyBg';
                $remove->size = [32, 20];
                $remove->alignment = 'CENTER';
                $remove->textColor = '#b24747';
                $remove->font->bold = true;
                $remove->font->size = 14;
                if ($remove->text == '0') {
                    $remove->opacity = 0.3;
                }
                
                $points = new UXVBox([$add, $remove]);
                $points->spacing = 4;
                $points->paddingLeft = 16;
                $points->alignment = 'CENTER_LEFT';
                
                // FINAL LINE
                $line = new UXHBox([$item[2], $title, $fiveContainer, $fourContainer, $threeContainer, $twoContainer, $points]);
                $line->spacing = 10; // Between logo and text
                $line->padding = 5; // Sell insets
                $line->alignment = 'CENTER_LEFT';
                if (strval($item[3]) == '–' and strval($item[4]) == '–' and strval($item[5]) == '–' and strval($item[6]) == '–') {
                    $line->opacity = 0.3;
                }
                $cell->text = null;
                $cell->graphic = $line;
            }
        });
        
        //$this->listView->items->add(['Рыженков Е.', '8А', self::setImage('good'), 11, 3, 0, 0]);
        //$this->listView->items->add(['Костенко М.', '8А', self::setImage('good'), 46, 0, 0, 0]);
        //$this->listView->items->add(['Мазекин(па) А.', '8А', self::setImage('bad'), 0, 6, 5, 3]);
        //$this->listView->items->add(['Королёв А.', '8А', self::setImage('normal'), 0, 6, 5, 0, '#FCD3A2']);
    }

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
        $this->loadForm('MainForm', false, true);
    }


}

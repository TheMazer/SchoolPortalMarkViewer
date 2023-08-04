<?php
namespace app\forms;

use std, gui, framework, app;


class MarksCount extends AbstractForm
{

    function showMarks($mFive, $mFour, $mThree, $mTwo, $fromDate, $toDate, $name) {
        // Values
        $this->fiveCounter->text = $mFive;
        $this->fourCounter->text = $mFour;
        $this->threeCounter->text = $mThree;
        $this->twoCounter->text = $mTwo;
        
        // Opacity
        if ($mFive > 0) { $this->markFiveSquare->opacity = 1; $this->fiveCounter->opacity = 1; } else { $this->markFiveSquare->opacity = 0.3; $this->fiveCounter->opacity = 0.3; $this->fiveCounter->text = '0'; }
        if ($mFour > 0) { $this->markFourSquare->opacity = 1; $this->fourCounter->opacity = 1; } else { $this->markFourSquare->opacity = 0.3; $this->fourCounter->opacity = 0.3; $this->fourCounter->text = '0'; }
        if ($mThree > 0) { $this->markThreeSquare->opacity = 1; $this->threeCounter->opacity = 1; } else { $this->markThreeSquare->opacity = 0.3; $this->threeCounter->opacity = 0.3; $this->threeCounter->text = '0'; }
        if ($mTwo > 0) { $this->markTwoSquare->opacity = 1; $this->twoCounter->opacity = 1; } else { $this->markTwoSquare->opacity = 0.3; $this->twoCounter->opacity = 0.3; $this->twoCounter->text = '0'; }
        if ($mFive > 0) { $this->plusCounter->opacity = 1; } else { $this->plusCounter->opacity = 0.3; }
        if ($mTwo > 0) { $this->minusCounter->opacity = 1; } else { $this->minusCounter->opacity = 0.3; }
        
        // Plus and Minus
        $this->plusCounter->text = $mFive * 1;
        $this->minusCounter->text = $mTwo * 5;
        
        // Name and Dates
        $this->markWindowTitle->text = 'Кол-во оценок за '. $fromDate. ' - '. $toDate;
        $this->markWindowSubtitle->text = $name;
        if ($fromDate == '' or $toDate == '') {
            $this->markWindowTitle->text = 'Кол-во оценок за эту неделю';
        }
    }
    
    /**
     * @event closeBtn.action 
     */
    function doCloseBtnAction(UXEvent $e = null)
    {    
        $this->hide();
    }



}

<?php
$I = new WebGuy($scenario);
$I->wantTo('perform actions and see result');
$I->amOnPage('/');
$I->seeResponseCodeIs(200);
$I->see('true');

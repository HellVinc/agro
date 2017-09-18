<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $offer common\models\Offer */

?>
<div class="offer">
    <div>
        <b><?= Html::encode($offer->creator->first_name . ' ' . $offer->creator->last_name) ?></b>,
        <?= Html::encode($offer->creator->Phone) ?>
    </div>
    <div><?= date('d.m.Y H:i', $offer->created_at) ?></div>
    <hr size="1" noshade="">
    <pre><?= Html::encode($offer->text) ?></pre>
</div>
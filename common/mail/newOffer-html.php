<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $offer common\models\Offer */

?>
<div class="offer">
    <b>
        <?= Html::encode($offer->creator->first_name . ' ' . $offer->creator->last_name) ?>,
        <?= Html::encode($offer->creator->Phone) ?>
    </b>
    <p><?= date('d.m.Y H:i', $offer->created_at) ?></p>
    <hr>
    <pre><?= Html::encode($offer->text) ?></pre>
</div>
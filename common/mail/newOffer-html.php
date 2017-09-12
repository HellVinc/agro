<?php
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $offer common\models\Offer */

?>
<div class="offer">
    <h1>The Agro received a new offer:</h1>
    <pre><?= Html::encode($offer->text) ?></pre>
</div>
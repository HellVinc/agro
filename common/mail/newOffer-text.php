<?php
/* @var $this yii\web\View */
/* @var $offer common\models\Offer */

echo $offer->creator->first_name .' '. $offer->creator->last_name .', '. $offer->creator->Phone;
?>

<?= date('d.m.Y H:i', $offer->created_at) ?>

----------------------------

<?= $offer->text ?>
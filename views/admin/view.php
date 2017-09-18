<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use budyaga\users\UsersAsset;
use budyaga\users\models\User;
use budyaga\users\components\UserPermissionsWidget;
use budyaga\users\components\PermissionsTreeWidget;

/* @var $this yii\web\View */
/* @var $model budyaga\users\models\User */

$this->title = $model->username;
$this->params['breadcrumbs'][] = ['label' => Yii::t('users', 'USERS'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$assets = UsersAsset::register($this);
?>
<div class="user-view">

    <h1><?= Html::encode($this->title)  ?></h1>

    <p>
        <?= Yii::$app->user->can('userUpdate', ['user' => $model]) ? Html::a(Yii::t('yii', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) : '' ?>
        <?= Yii::$app->user->can('userDelete') ? Html::a(Yii::t('yii', 'Delete'), ['delete', 'id' => $model->id], ['class' => 'btn btn-danger', 'data' => ['confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'), 'method' => 'post']]) : ''?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'username',
            'email:email',
            [
                'attribute' => 'photo',
                'value' => ($model->photo) ? $model->photo : $assets->baseUrl . '/img/' . $model->getDefaultPhoto() . '.png',
                'format' => ['image', ['width' => 200, 'height' => 200]]
            ],
            [
                'attribute' => 'sex',
                'value' => User::getSexArray()[$model->sex]
            ],
            [
                'attribute' => 'status',
                'value' => User::getStatusArray()[$model->status]
            ],
            'created_at:datetime',
            'updated_at:datetime',
            'about',
            'phone',
//            'description',
           [    'attribute' => 'description',
                'value' => function($row) use ($resume) {
                    $uploadDir = Yii::getAlias('@frontend/web');

                   $links = '<ul>';
                   foreach ($resume as $file) {
                      $links .= '<li>'.Html::a($file->link, $file->link).'</li>';


                   }
                   return $links.'</ul>';
                },
               'format' => 'raw'
            ],
            'kurs',
            [
                'attribute' => 'teacher',
                'value' => function($row) {
                    return $row->teacher == 1 ? 'Да' : 'Нет';
                }
            ]
        ],
    ]) ?>

    <h2><?= Yii::t('users', 'USER_PERMISSIONS')?></h2>
    <p>
        <?= Yii::$app->user->can('userPermissions', ['user' => $model]) ? Html::a(Yii::t('yii', 'Update'), ['permissions', 'id' => $model->id], ['class' => 'btn btn-primary']) : '' ?>
    </p>

    <?= PermissionsTreeWidget::widget(['user' => $model])?>

</div>

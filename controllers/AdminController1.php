<?php

namespace budyaga\users\controllers;

use Yii;
use budyaga\users\models\User;
use backend\models\Kurs;
use backend\models\UserFiles;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use budyaga\users\models\forms\AssignmentForm;
use yii\filters\AccessControl;

/**
 * AdminController implements the CRUD actions for User model.
 */
class AdminController extends Controller
{
    private $_model = false;

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index', 'create', 'view', 'update', 'delete', 'permissions'],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index'],
                        'roles' => ['userManage'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['create'],
                        'roles' => ['userCreate'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['view'],
                        'roles' => ['userView'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['update'],
                        'matchCallback' => function ($rule, $action) {
                            return Yii::$app->user->can('userUpdate', ['user' => $this->findModel(Yii::$app->request->get('id'))]);
                        }
                    ],
                    [
                        'allow' => true,
                        'actions' => ['delete'],
                        'roles' => ['userDelete'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['permissions'],
                        'roles' => ['userPermissions'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Lists all User models.
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => User::find(),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single User model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
            'resume' => UserFiles::find()->where(['user_id' => $id])->all(),
        ]);
    }

    /**
     * Creates a new User model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new User(['scenario' => 'add']);
        $kurs = Kurs::find()->all();

        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

        if ($model->load(Yii::$app->request->post())) {

            $model->setPassword($model->password);
            $model->generateAuthKey();

            if(Yii::$app->request->post('User')['teacher'] == 1) {
                $model->type = 1;
            }
            else {
                $model->type = 0; 
            }

            if($model->save()) {
                // send mail to user 
                if($model->teacher == 1) {
                    // send to teacher
                    mail($model->email, 'Вас назначили преподавателем', "
                        <h3>Вас назначили преподавателем</h3>

                        <p>Для входа перейлите в <a href=\"http://academy.evne.pro/admin\">кабинет</a></p>

                        <ul>
                        <li>Ваша почта - {$model->email}</li>
                        <li>Ваш пароль - {$model->password}</li>
                        </ul>
                    ", $headers);
                }
                else {
                    switch ($model->status) {
                        case 1:
                            mail($model->email, 'Заявка на запись', "
                                <h3>Вы подали заявку на запись в студенты академии</h3>

                                <p>Ожидайте подтверждения от администрации.</p>
                            ", $headers);
                            break;
                        case 2:
                            mail($model->email, 'Вас добавили в студенты', "
                                <h3>Вас добавили в студенты</h3>

                                <p>Для входа перейлите в <a href=\"http://academy.evne.pro/profile\">кабинет</a></p>

                                <ul>
                                <li>Ваша почта - {$model->email}</li>
                                <li>Ваш пароль - <i>указан при регистрации</i></li>
                                </ul>
                            ", $headers);
                            $zapis = new \backend\models\Zapis;
                            $zapis->id_student = $model->id;
                            $zapis->id_kursa = $model->kurs;
                            $zapis->save();
                            break;
                    }
                }


                return $this->redirect(['view', 'id' => $model->id]);
            }
            else {
                return $this->render('create', [
                    'model' => $model,
                    'kurs' => $kurs,
                ]);
            }
        } else {
            return $this->render('create', [
                'model' => $model,
                'kurs' => $kurs,
            ]);
        }
    }

    /**
     * Updates an existing User model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $kurs = Kurs::find()->all();
        $resume = UserFiles::find()->where(['user_id' => $id])->all();

        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

        if ($model->load(Yii::$app->request->post())) {

            if($model->password) {
                $model->setPassword();
                $model->generateAuthKey();
            }

            if($model->teacher == 1) {
                $model->type = 1;
            }
            else {
                $model->type = 0; 
            }

            $action = false;
            if($model->status != $model->oldAttributes->status) {
                $action = $model->status;
            }

            if($model->save()) {

                if($action !== false) {
                    switch ($action) {
                        case 1:
                            mail($model->email, 'Заявка на запись', "
                                <h3>Вы подали заявку на запись в студенты академии</h3>

                                <p>Ожидайте подтверждения от администрации.</p>
                            ", $headers);
                            break;
                        case 2:
                            mail($model->email, 'Вас добавили в студенты', "
                                <h3>Вас добавили в студенты</h3>

                                <p>Для входа перейлите в <a href=\"http://academy.evne.pro/profile\">кабинет</a></p>

                                <ul>
                                <li>Ваша почта - {$model->email}</li>
                                <li>Ваш пароль - <i>указан при регистрации</i></li>
                                </ul>
                            ", $headers);
                            if(!\backend\models\Zapis::find()->where(['id_student' => $model->id])->andWhere(['id_kursa' => $model->kurs])->one()) {
                                $zapis = new \backend\models\Zapis;
                                $zapis->id_student = $model->id;
                                $zapis->id_kursa = $model->kurs;
                                $zapis->save();
                            }
                            break;
                    }
                }


                return $this->redirect(['view', 'id' => $model->id]);
            }
            else {
                return $this->render('create', [
                    'model' => $model,
                    'kurs' => $kurs,
                ]);
            }
        } else {
            return $this->render('update', [
                'model' => $model,
                'kurs' => $kurs,
                'resume' => $resume,

            ]);
        }
    }

    /**
     * Deletes an existing User model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    public function actionPermissions($id)
    {
        $modelForm = new AssignmentForm;
        $modelForm->model = $this->findModel($id);

        if ($modelForm->load(Yii::$app->request->post()) && $modelForm->save()) {
            Yii::$app->session->setFlash('success', Yii::t('users', 'SUCCESS_UPDATE_PERMISSIONS'));
            return $this->redirect(['view', 'id' => $id]);
        }

        return $this->render('permissions', [
            'modelForm' => $modelForm
        ]);
    }

    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if ($this->_model === false) {
            $this->_model = User::findOne($id);
        }

        if ($this->_model !== null) {
            return $this->_model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}

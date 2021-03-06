<?php

declare(strict_types=1);

namespace app\controllers;

use app\core\Application;
use app\core\Controller;
use app\core\exception\ForbiddenException;
use app\core\exception\NotFoundException;
use app\core\middlewares\AuthMiddleware;
use app\core\Request;
use app\models\Timestamp;
use app\models\Shift;

class TimestampController extends Controller
{
    public function __construct()
    {
        $this->registerMiddleware(new AuthMiddleware(['get', 'post', 'put']));
    }

    public function get(Request $request)
    {
        if ($request->isGet() === false) {
            throw new NotFoundException;
        }

        $data = $request->getBody();

        if (isset($data['shift_id'])) {
            return Timestamp::findAll([['shift_id', '=', $data['shift_id']]]);
        }

        $shiftIds = array_map(fn ($shift) => $shift['id'], Shift::findAll([['user_id', '=', Application::$app->user->id]]));

        if (count($shiftIds) === 0) {
            return [];
        }

        return Timestamp::findAll([
            ['shift_id', 'IN', '(' . implode(',', $shiftIds) . ')']
        ]);
    }

    public function post(Request $request)
    {
        if ($request->isPost() === false) {
            throw new NotFoundException;
        }

        $data = $request->getBody();

        if (!isset($data['shift_id'])) {
            return "You must specify shift_id";
        }

        if (!isset($data['from'])) {
            return "You must specify 'from' timestamp";
        }

        $shift_id = intval($data['shift_id']);
        $timestamp = new Timestamp();
        $timestamp->loadData($data);

        if (Shift::findOne(['id' => $shift_id])->user_id !== Application::$app->user->id) {
            throw new ForbiddenException;
        }

        if ($timestamp->validate($request->getBody()) && $timestamp->save()) {
            return $timestamp->getData();
        }

        return $timestamp->formatErrors();
    }

    public function put(Request $request)
    {
        if ($request->isPut() === false) {
            throw new NotFoundException;
        }

        $data = $request->getBody();

        if (!isset($data['shift_id'])) {
            return "You must specify shift_id";
        }

        if (!isset($data['to'])) {
            return "You must specify 'to' timestamp";
        }

        $shift_id = intval($data['shift_id']);
        $timestamp = Timestamp::findOne(['shift_id' => $shift_id]);

        if (Application::$app->user->isAdmin() === false) {
            if (Shift::findOne(['id' => $shift_id])->user_id !== Application::$app->user->id) {
                throw new ForbiddenException;
            }
        }

        $timestamp->loadData($data);

        if ($timestamp->validate()) {
            if ($timestamp->update(['id' => $timestamp->id, 'to' => $data['to']])) {
                return $timestamp->getData();
            }
        }

        return $timestamp->formatErrors();
    }
}

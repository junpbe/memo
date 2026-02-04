<?php

namespace App\Exceptions;

use Exception;

/**
 * モデルが他の人に更新されていた場合に発生する例外。
 */
class ModelNotLatestException extends Exception
{
}

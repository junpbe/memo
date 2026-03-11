<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * モデルが他の人に更新されていた場合に発生する例外。
 */
class ModelNotLatestException extends RuntimeException
{
}

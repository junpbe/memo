<?php

namespace App\Livewire\Forms;

use App\Models\Memo;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Form;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Validate;
use LogicException;

/**
 * メモフォーム。
 */
class MemoForm extends Form
{
    /** @var int id */
    #[Locked]
    public ?int $id = null;

    /** @var int 新規追加リストのキー */
    #[Locked]
    public ?int $new_key = null;

    /** @var \Carbon\CarbonImmutable updated_at */
    #[Locked]
    public ?CarbonImmutable $updated_at = null;

    /** @var string 本文 */
    #[Validate]
    public ?string $body = null;

    /**
     * rules.
     *
     * @return string[][]
     */
    protected function rules()
    {
        return [
            'body' => [
                'required',
            ],
        ];
    }

    /**
     * messages.
     *
     * @return array
     */
    protected function messages()
    {
        return [
            //
        ];
    }

    /**
     * validationAttributes.
     *
     * @return string[]
     */
    protected function validationAttributes()
    {
        return [
            'body' => 'メモ本文',
        ];
    }

    /**
     * モデルセット。
     *
     * @param \App\Models\Memo $model
     */
    public function setModel(Memo $model): void
    {
        $this->id = $model->id;
        $this->updated_at = $model->updated_at;

        $this->body = $model->body;
    }

    /**
     * モデルが存在しているかどうか。
     *
     * @return bool モデルが存在している場合true、そうでない場合false
     */
    public function modelExists(): bool
    {
        // モデルが存在している場合はidがセットされている
        if (isset($this->id)) {
            return true;
        }

        return false;
    }

    /**
     * 保存。
     */
    public function save(): void
    {
        // モデルが存在しない場合は新規
        if (!$this->modelExists()) {
            $this->store();
            return;
            // TODO 新規の時は新規リストから消して、本リストを更新
        }

        $this->update();
    }

    /**
     * 追加。
     */
    public function store(): void
    {
        // 新規追加はidがセットされていない場合のみ
        if (isset($this->id)) {
            throw new LogicException('idがセットされている状態で呼び出されました。');
        }

        // 権限チェック
        Gate::authorize('create', Memo::class);

        $this->validate();

        $model = new Memo();
        $model->user_id = Auth::id();
        $model->body = $this->body;
        $model->save();

        $this->setModel($model);
        //TODO　新規の時は新規リストから消して、本リストを更新
    }

    /**
     * 更新。
     */
    public function update(): void
    {
        // 更新はidがセットされている場合のみ
        if (!isset($this->id)) {
            throw new LogicException('idがセットされていない状態で呼び出されました。');
        }

        $this->validate();

        $model = Memo::lockLatest($this->id, $this->updated_at);

        // 権限チェック
        Gate::authorize('update', $model);

        $model->body = $this->body;
        $model->save();

        $this->setModel($model);
    }
}

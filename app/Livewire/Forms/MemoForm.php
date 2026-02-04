<?php

namespace App\Livewire\Forms;

use App\Models\Memo;
use Illuminate\Support\Facades\Auth;
use Livewire\Form;
use Livewire\Attributes\Validate;
use LogicException;

class MemoForm extends Form
{
    /** @var \App\Models\Memo モデル */
    public ?Memo $model = null;

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
        $this->model = $model;

        $this->body = $model->body;
    }

    /**
     * 保存。
     */
    public function save(): void
    {
        // 新規の場合はまずモデルを作る
        if (!isset($this->model)) {
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
        // 新規追加はモデルがセットされていない場合のみ
        if (isset($this->model)) {
            throw new LogicException('モデルが存在している状態で呼び出されました。');
        }

        $this->validate();

        $this->model = new Memo();
        $this->model->user_id = Auth::id();
        $this->model->body = $this->body;
        $this->model->save();
        //TODO　新規の時は新規リストから消して、本リストを更新
    }

    /**
     * 更新。
     */
    public function update(): void
    {
        $this->validate();
        $this->model->lockLatest();

        $this->model->body = $this->body;
        $this->model->save();
    }
}

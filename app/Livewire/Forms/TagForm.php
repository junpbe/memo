<?php

namespace App\Livewire\Forms;

use App\Models\Tag;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Livewire\Form;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Validate;

/**
 * タグフォーム。
 */
class TagForm extends Form
{
    /** @var int id */
    #[Locked]
    public ?int $id = null;

    /** @var \Carbon\CarbonImmutable updated_at */
    #[Locked]
    public ?CarbonImmutable $updated_at = null;

    /** @var string 名前 */
    #[Validate]
    public ?string $name = null;

    /**
     * rules.
     *
     * @return string[][]
     */
    protected function rules()
    {
        return [
            'name' => [
                'required',
                'max:50',
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
            'name' => 'タグ',
        ];
    }

    /**
     * モデルセット。
     *
     * @param \App\Models\Tag $model
     */
    public function setModel(Tag $model): void
    {
        $this->id = $model->id;
        $this->updated_at = $model->updated_at;

        $this->name = $model->name;
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
        }

        $this->update();
    }

    /**
     * 追加。
     */
    protected function store(): void
    {
        // 権限チェック
        Gate::authorize('create', Tag::class);

        $this->validate();

        // 優先度を取得するためにテーブルロック
        $priority = Tag::lockForUpdate()->max('priority') + 1;

        $model = new Tag();
        $model->user_id = Auth::id();
        $model->name = $this->name;
        $model->priority = $priority;
        $model->save();

        $this->setModel($model);
    }

    /**
     * 更新。
     */
    protected function update(): void
    {
        $this->validate();

        $model = Tag::lockLatest($this->id, $this->updated_at);

        // 権限チェック
        Gate::authorize('update', $model);

        $model->name = $this->name;
        $model->save();

        $this->setModel($model);
    }
}
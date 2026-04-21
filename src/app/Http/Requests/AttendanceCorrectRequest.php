<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceCorrectRequest extends FormRequest
{
    /**
     * ユーザーがこのリクエストを行う権限があるか
     */
    public function authorize()
    {
        return true; // ログイン済みであればOK
    }

    /**
     * バリデーションルール
     */
    public function rules()
    {
        return [
            'punch_in' => ['required', 'date_format:H:i'],
            'punch_out' => ['required', 'date_format:H:i', 'after:punch_in'],
            'break_start' => ['nullable', 'date_format:H:i', 'after:punch_in', 'before:punch_out'],
            'break_end' => ['nullable', 'date_format:H:i', 'after:break_start', 'before:punch_out'],
            'remark' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * エラーメッセージ
     */
    public function messages()
    {
        return [
            'punch_in.date_format' => '出勤時間もしくは退勤時間が不適切な値です',
            'punch_out.date_format' => '出勤時間もしくは退勤時間が不適切な値です',
            'punch_out.after' => '出勤時間もしくは退勤時間が不適切な値です',

            'break_start.after' => '休憩時間が不適切な値です',
            'break_start.before' => '休憩時間が不適切な値です',
            'break_end.after' => '休憩時間が不適切な値です',
            'break_end.before' => '休憩時間が不適切な値です',

            'remark.required' => '備考を記入してください',
        ];
    }
}
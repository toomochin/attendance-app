# 勤怠管理システム

## 環境構築

1. Dockerを起動する

2. プロジェクト直下で、以下のコマンドを実行する

```bash
make init
```
※Makefileを使用し、コンテナの起動、ライブラリインストール、DB構築、ストレージ権限の設定（chmod 777）を一括で行います。<br>

## メール認証
開発環境ではメール確認用に Mailpit を使用しています。
プロジェクト起動後、ブラウザで以下のURLにアクセスすると、システムから送信されたメール（会員登録時の認証メール等）を確認できます。

http://localhost:8025

.envファイルの設定（初期設定済み）：

MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_FROM_ADDRESS=admin@example.com

以下のリンクは公式ドキュメントです。<br>
https://docs.stripe.com/payments/checkout?locale=ja-JP
## テーブル仕様
### usersテーブル
| カラム名 | 型 | primary key | unique key | not null | foreign key |
| --- | --- | --- | --- | --- | --- |
| id | bigint | ◯ |  | ◯ |  |
| name | varchar(255) |  |  | ◯ |  |
| email | varchar(255) |  | ◯ | ◯ |  |
| password | varchar(255) |  |  | ◯ |  |
| role | int |  |  | ◯ |  |0:一般, 1:管理者
| created_at | timestamp |  |  |  |  |
| updated_at | timestamp |  |  |  |  |

### attendancesテーブル
| カラム名 | 型 | primary key | unique key | not null | foreign key |
| --- | --- | --- | --- | --- | --- |
| id | bigint | ◯ |  | ◯ |  |
| user_id | bigint |  |  | ◯ | users(id) |
| date | date |  |  | ◯ |  |
| punch_in | time |  |  | ◯ |  |
| punch_out | time |  |  |  |  |
| break_in | time |  |  |  |  |
| break_out | time |  |  |  |  |
| break2_in | time |  |  |  |  |
| break2_out | time |  |  |  |  |

### attendance_correct_requestsテーブル
| カラム名 | 型 | primary key | unique key | not null | foreign key |
| --- | --- | --- | --- | --- | --- |
| id | bigint | ◯ |  | ◯ |  |
| attendance_id | bigint |  |  | ◯ | attendances(id) |
| user_id | bigint |  |  | ◯ | users(id) |
| punch_in | time |  |  | ◯ |  |
| punch_out | time |  |  | ◯ |  |
| break_in | time |  |  |  |  |
| break_out | time |  |  |  |  |
| break2_in | time |  |  |  |  |
| break2_out | time |  |  |  |  |
| remark | text |  |  | ◯ |  |
| status | int |  |  | ◯ |  |

## ER図
![alt](ER.png)

##テストアカウント
php artisan db:seed を実行することで、以下のアカウントが作成されます。

管理者
メールアドレス: admin@gmail.com
パスワード: password
権限: 管理者 (role: 1)

一般スタッフ1
メールアドレス: general1@gmail.com
パスワード: password
権限: 一般 (role: 0)

一般スタッフ2
メールアドレス: general2@gmail.com
パスワード: password
権限: 一般 (role: 0)

## テストの実行
システム全体の整合性を確認するために、以下のコマンドでテストを実行できます。
```bash
# 1. テスト用データベースの作成（初回のみ）
docker-compose exec mysql mysql -u root -p -e "create database if not exists test_database;"
# パスワードは root を入力

# 2. テストの実行
docker-compose exec php php artisan test
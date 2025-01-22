# PHP 動的SQLセッション管理ライブラリ

MySQLデータベースを使用したカスタマイズ可能なPHPライブラリです。このライブラリは、PDOを利用してセキュアなSQL操作を実現し、柔軟なテーブル名の指定をサポートします。

## 主な機能

- **カスタムセッション管理**: セッションの開始、挿入、削除、取得を簡単に実行可能。
- **動的テーブル名のサポート**: 任意のテーブルをセッション管理に使用可能。
- **セキュリティとスケーラビリティ**: SQLインジェクション対策を施したPDOを利用。
- **クッキー統合**: セッション用クッキー (`SUID` および `AUTHID`) を自動管理。

## 必要条件

- PHP 8.0以上
- MySQL 5.7以上
- PDO拡張が有効化されていること

## インストール

1. このリポジトリをクローンするか、スクリプトをプロジェクトにコピーします:
   ```bash
   git clone https://github.com/star-dot123/PHP-session_withMySQL.git
   ```

2. データベースに以下のテーブルを作成します:
   ```sql
   CREATE TABLE `sessions` (
       `id` INT AUTO_INCREMENT PRIMARY KEY,
       `session_id` VARCHAR(32) NOT NULL,
       `auth_id` VARCHAR(32) NOT NULL,
       `data` TEXT NOT NULL,
       `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
       `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
   );
   ```

3. スクリプト内のデータベース接続情報を編集します:
   ```php
   $dsn = 'mysql:host=localhost;dbname=your_database;charset=utf8';
   $username = 'your_username';
   $password = 'your_password';
   ```

## 使い方

### セッションを開始する
```php
sql_session('start');
```

### データを挿入または更新する
```php
sql_session('inset', 'key_name', 'value');
```

### データを取得する
```php
$value = sql_session('take', 'key_name');
```

### 全データを取得する
```php
$data = sql_session('take_all');
```

### データを削除する
```php
sql_session('delete', 'key_name');
```

## ライセンス

このプロジェクトはMITライセンスの下で公開されています。詳細は[LICENSE](LICENSE)ファイルをご覧ください。

---
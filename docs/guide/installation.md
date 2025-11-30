# インストール

## 要件

- PHP 8.4 以上

## Composer によるインストール

Composer を使用してインストールできます。

```bash
composer require wiz-develop/php-value-object
```

## 依存ライブラリ

このライブラリは以下のライブラリに依存しています。

### wiz-develop/php-monad

`Result` 型や `Option` 型を提供するモナドライブラリです。値オブジェクトのファクトリメソッド (`tryFrom` など) が返す `Result` 型はこのライブラリで定義されています。

```bash
# php-value-object をインストールすると自動的にインストールされます
composer require wiz-develop/php-monad
```

詳細は [php-monad のドキュメント](https://github.com/wiz-develop/php-monad) を参照してください。

## 開発環境のセットアップ

### Dev Container (推奨)

このプロジェクトは Dev Container をサポートしています。VS Code と Docker を使用している場合、自動的に開発環境がセットアップされます。

1. VS Code で Dev Containers 拡張機能をインストール
2. プロジェクトを開く
3. 「Reopen in Container」を選択

### 手動セットアップ

```bash
# リポジトリをクローン
git clone https://github.com/wiz-develop/php-value-object.git
cd php-value-object

# 依存関係をインストール
composer install

# テストを実行
./vendor/bin/phpunit

# 静的解析を実行
./vendor/bin/phpstan analyse
```

## 次のステップ

- [コンセプト](/guide/concepts) - 値オブジェクトパターンの詳細
- [クイックスタート](/guide/quick-start) - 基本的な使い方

# コンセプト

## 値オブジェクトパターン

値オブジェクト (Value Object) は、ドメイン駆動設計 (DDD) における重要な設計パターンの 1 つです。

### エンティティとの違い

| 特性 | エンティティ | 値オブジェクト |
|------|--------------|----------------|
| 識別子 | 持つ (ID) | 持たない |
| 等価性 | ID で判断 | 値で判断 |
| 可変性 | 可変 | 不変 |
| ライフサイクル | 長い | 短い |

### 値オブジェクトの例

日常的に扱う概念の多くは値オブジェクトとして表現できます。

- 金額 (100 円)
- メールアドレス (example@example.com)
- 日付 (2025-05-14)
- 住所 (東京都渋谷区...)
- 色 (#FF0000)

## このライブラリの設計原則

### 1. 不変性 (Immutability)

すべての値オブジェクトは `readonly` クラスとして実装されています。一度作成されたインスタンスは変更できません。

```php
// ❌ これはできない
$date->year = 2026;

// ✅ 新しいインスタンスを作成する
$nextYear = $date->addYears(1);
```

### 2. ファクトリメソッドによる生成

コンストラクタは `private` で、ファクトリメソッドを通じてのみインスタンスを作成できます。

```php
// from: 信頼できる値から作成 (検証失敗時は例外)
$email = EmailAddress::from("test@example.com");

// tryFrom: 信頼できない値から作成 (Result 型を返す)
$result = EmailAddress::tryFrom($userInput);
```

### 3. Result 型によるエラーハンドリング

`tryFrom` メソッドは `Result` 型を返します。これにより、例外を使わずにエラーを扱えます。

```php
$result = EmailAddress::tryFrom($userInput);

if ($result->isOk()) {
    $email = $result->unwrap();
    // 成功時の処理
} else {
    $error = $result->unwrapErr();
    // エラー時の処理
}
```

### 4. Option 型による null 安全性

`fromNullable` メソッドは `Option` 型を返します。null を安全に扱えます。

```php
$option = EmailAddress::fromNullable($maybeNull);

if ($option->isSome()) {
    $email = $option->unwrap();
}

// または
$email = $option->unwrapOr(EmailAddress::from("default@example.com"));
```

## ファクトリメソッドの使い分け

| メソッド | 入力 | 戻り値 | 用途 |
|----------|------|--------|------|
| `from` | 非 null | インスタンス | 信頼できる値から作成 |
| `tryFrom` | 非 null | `Result` | 検証が必要な値から作成 |
| `fromNullable` | nullable | `Option` | null 許容の値から作成 |
| `tryFromNullable` | nullable | `Result<Option>` | 検証 + null 許容 |

### 使い分けの例

```php
// データベースから取得した値 (信頼できる)
$email = EmailAddress::from($row['email']);

// ユーザー入力 (信頼できない)
$result = EmailAddress::tryFrom($request->input('email'));

// 任意入力フィールド (null の可能性がある)
$option = EmailAddress::fromNullable($request->input('secondary_email'));

// 任意入力 + 検証が必要
$result = EmailAddress::tryFromNullable($request->input('secondary_email'));
```

## 次のステップ

- [クイックスタート](/guide/quick-start) - 具体的な使用例
- [チュートリアル](/tutorial/) - 各値オブジェクトの詳細な使い方

# 拡張ガイド

このセクションでは、PHP Value Object ライブラリを拡張して独自の値オブジェクトを作成する方法を解説します。

## 概要

既存の値オブジェクトクラスを継承することで、ドメイン固有の制約を持つ値オブジェクトを作成できます。

## ValueObjectMeta 属性

カスタム値オブジェクトには `ValueObjectMeta` 属性を付与することで、エラーメッセージなどで使用される名前を設定できます。

```php
use WizDevelop\PhpValueObject\ValueObjectMeta;

#[ValueObjectMeta(name: '商品コード', description: '商品を識別するコード')]
final readonly class ProductCode extends StringValue
{
    // ...
}
```

### パラメータ

| パラメータ | 型 | 説明 |
|------------|-----|------|
| `name` | `string` | 値オブジェクトの表示名 |
| `description` | `?string` | 値オブジェクトの説明 (任意) |

## 拡張の基本パターン

### 1. 基底クラスを継承

```php
final readonly class MyValue extends StringValue
{
}
```

### 2. final readonly を指定

値オブジェクトは不変であるべきなので、`final readonly` を指定します。

### 3. 制約メソッドをオーバーライド

```php
#[Override]
public static function minLength(): int
{
    return 5;
}
```

### 4. コンストラクタは private

ファクトリメソッドを通じてのみインスタンス化できるよう、コンストラクタは private にします (基底クラスで既に private)。

## 内容

### [カスタム文字列](/extension/custom-string)

文字列系の値オブジェクトをカスタマイズする方法を学びます。

- 文字数制限の設定
- 正規表現パターンの設定
- カスタムバリデーション

### [カスタム数値](/extension/custom-number)

数値系の値オブジェクトをカスタマイズする方法を学びます。

- 範囲制限の設定
- カスタム演算メソッド

### [カスタム日時](/extension/custom-datetime)

日時系の値オブジェクトをカスタマイズする方法を学びます。

- 営業日の計算
- 特殊な日付制約

### [カスタムコレクション](/extension/custom-collection)

コレクション系の値オブジェクトをカスタマイズする方法を学びます。

- 要素数制限
- 型制約付きコレクション

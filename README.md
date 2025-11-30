# PHP Value Object

[![Packagist Version](https://img.shields.io/packagist/v/wiz-develop/php-value-object)](https://packagist.org/packages/wiz-develop/php-value-object)
[![PHP Version](https://img.shields.io/packagist/php-v/wiz-develop/php-value-object)](https://packagist.org/packages/wiz-develop/php-value-object)
[![PHPStan](https://github.com/wiz-develop/php-value-object/actions/workflows/phpstan.yml/badge.svg)](https://github.com/wiz-develop/php-value-object/actions/workflows/phpstan.yml)
[![Documentation](https://github.com/wiz-develop/php-value-object/actions/workflows/deploy-docs.yml/badge.svg)](https://github.com/wiz-develop/php-value-object/actions/workflows/deploy-docs.yml)
[![License](https://img.shields.io/packagist/l/wiz-develop/php-value-object)](https://github.com/wiz-develop/php-value-object/blob/main/LICENSE)

不変性、型安全性、自己検証を持つドメイン値オブジェクトを提供する PHP ライブラリです。

## インストール

```bash
composer require wiz-develop/php-value-object
```

## 使用例

### カスタム値オブジェクトの作成

```php
use WizDevelop\PhpValueObject\String\StringValue;
use WizDevelop\PhpValueObject\ValueObjectMeta;

#[ValueObjectMeta(name: '商品コード')]
final readonly class ProductCode extends StringValue
{
    protected static function minLength(): int { return 5; }
    protected static function maxLength(): int { return 5; }
    protected static function regex(): string { return '/^P[0-9]{4}$/'; }
}

$code = ProductCode::from('P1234');
```

### Result 型によるエラーハンドリング

```php
use WizDevelop\PhpMonad\Result;

$result = ProductCode::tryFrom('invalid');

$code = $result
    ->map(fn($code) => $code->value())
    ->unwrapOr('デフォルト');
```

## ドキュメント

詳細なガイドと API リファレンスは [ドキュメントサイト](https://wiz-develop.github.io/php-value-object/) を参照してください。

## 要件

- PHP 8.4 以上

## ライセンス

MIT License

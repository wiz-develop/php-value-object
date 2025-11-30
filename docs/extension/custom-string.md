# カスタム文字列値オブジェクト

StringValue を継承してカスタム文字列値オブジェクトを作成する方法を解説します。

## オーバーライド可能なメソッド

| メソッド | 戻り値 | 説明 |
|----------|--------|------|
| `minLength()` | `int` | 最小文字数 |
| `maxLength()` | `int` | 最大文字数 |
| `regex()` | `string` | 正規表現パターン |
| `isValid()` | `Result` | カスタムバリデーション |

## 基本的な例

### 商品コード

5 文字固定で、先頭が P、続く 4 文字が数字の商品コード。

```php
use Override;
use WizDevelop\PhpValueObject\String\StringValue;
use WizDevelop\PhpValueObject\ValueObjectMeta;

#[ValueObjectMeta(name: '商品コード')]
final readonly class ProductCode extends StringValue
{
    #[Override]
    public static function minLength(): int
    {
        return 5;
    }

    #[Override]
    public static function maxLength(): int
    {
        return 5;
    }

    #[Override]
    protected static function regex(): string
    {
        return '/^P[0-9]{4}$/';
    }
}
```

### 使用例

```php
// 有効な商品コード
$code = ProductCode::from("P1234");

// 検証付き
$result = ProductCode::tryFrom("P0001");
if ($result->isOk()) {
    $code = $result->unwrap();
}

// 無効な商品コード
$result = ProductCode::tryFrom("INVALID");
$result->isErr(); // true
```

### 電話番号

日本の電話番号形式。

```php
#[ValueObjectMeta(name: '電話番号')]
final readonly class PhoneNumber extends StringValue
{
    #[Override]
    public static function minLength(): int
    {
        return 10;
    }

    #[Override]
    public static function maxLength(): int
    {
        return 13;
    }

    #[Override]
    protected static function regex(): string
    {
        // 0から始まる10〜13桁の数字とハイフン
        return '/^0[0-9]{1,4}-?[0-9]{1,4}-?[0-9]{3,4}$/';
    }

    /**
     * ハイフンなしの形式で取得
     */
    public function toDigitsOnly(): string
    {
        return str_replace('-', '', $this->value);
    }

    /**
     * ハイフン付きの標準形式で取得
     */
    public function toFormatted(): string
    {
        $digits = $this->toDigitsOnly();

        // 携帯電話 (090, 080, 070)
        if (preg_match('/^0[789]0/', $digits)) {
            return substr($digits, 0, 3) . '-' .
                   substr($digits, 3, 4) . '-' .
                   substr($digits, 7, 4);
        }

        // 固定電話 (その他)
        return $digits;
    }
}
```

### 郵便番号

```php
#[ValueObjectMeta(name: '郵便番号')]
final readonly class PostalCode extends StringValue
{
    #[Override]
    public static function minLength(): int
    {
        return 7;
    }

    #[Override]
    public static function maxLength(): int
    {
        return 8; // ハイフン付きの場合
    }

    #[Override]
    protected static function regex(): string
    {
        return '/^[0-9]{3}-?[0-9]{4}$/';
    }

    public function toFormatted(): string
    {
        $digits = str_replace('-', '', $this->value);
        return substr($digits, 0, 3) . '-' . substr($digits, 3, 4);
    }
}
```

## カスタムバリデーション

`isValid()` メソッドをオーバーライドして、正規表現だけでは表現できない複雑なバリデーションを追加できます。

### URL

```php
use WizDevelop\PhpMonad\Result;
use WizDevelop\PhpValueObject\Error\ValueObjectError;

#[ValueObjectMeta(name: 'URL')]
final readonly class Url extends StringValue
{
    #[Override]
    public static function minLength(): int
    {
        return 10;
    }

    #[Override]
    public static function maxLength(): int
    {
        return 2048;
    }

    #[Override]
    protected static function regex(): string
    {
        return '/^https?:\/\/.+$/';
    }

    #[Override]
    protected static function isValid(string $value): Result
    {
        // filter_var で追加検証
        if (filter_var($value, FILTER_VALIDATE_URL) === false) {
            return Result\err(ValueObjectError::string()->invalidRegex(
                className: static::class,
                regex: 'URL format',
                value: $value,
            ));
        }

        return Result\ok(true);
    }

    public function getHost(): string
    {
        return parse_url($this->value, PHP_URL_HOST) ?? '';
    }

    public function getPath(): string
    {
        return parse_url($this->value, PHP_URL_PATH) ?? '';
    }
}
```

### スラッグ

URL に使用できる文字列。

```php
#[ValueObjectMeta(name: 'スラッグ')]
final readonly class Slug extends StringValue
{
    #[Override]
    public static function minLength(): int
    {
        return 1;
    }

    #[Override]
    public static function maxLength(): int
    {
        return 100;
    }

    #[Override]
    protected static function regex(): string
    {
        // 小文字英数字とハイフンのみ
        return '/^[a-z0-9]+(?:-[a-z0-9]+)*$/';
    }

    /**
     * 文字列からスラッグを生成
     */
    public static function fromString(string $input): static
    {
        $slug = mb_strtolower($input);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');

        return static::from($slug);
    }
}
```

## ドメイン固有のメソッド

値オブジェクトにドメイン固有のメソッドを追加して、より表現力のあるコードを書けます。

### クレジットカード番号

```php
#[ValueObjectMeta(name: 'クレジットカード番号')]
final readonly class CreditCardNumber extends StringValue
{
    #[Override]
    public static function minLength(): int
    {
        return 13;
    }

    #[Override]
    public static function maxLength(): int
    {
        return 19;
    }

    #[Override]
    protected static function regex(): string
    {
        return '/^[0-9]{13,19}$/';
    }

    #[Override]
    protected static function isValid(string $value): Result
    {
        // Luhn アルゴリズムで検証
        if (!self::validateLuhn($value)) {
            return Result\err(ValueObjectError::string()->invalidRegex(
                className: static::class,
                regex: 'Luhn check',
                value: $value,
            ));
        }

        return Result\ok(true);
    }

    /**
     * カードブランドを取得
     */
    public function getBrand(): string
    {
        return match (true) {
            str_starts_with($this->value, '4') => 'Visa',
            str_starts_with($this->value, '5') => 'Mastercard',
            str_starts_with($this->value, '3') => 'American Express',
            default => 'Unknown',
        };
    }

    /**
     * マスクされた番号を取得
     */
    public function masked(): string
    {
        return str_repeat('*', strlen($this->value) - 4) .
               substr($this->value, -4);
    }

    private static function validateLuhn(string $number): bool
    {
        // Luhn アルゴリズムの実装
        $sum = 0;
        $length = strlen($number);

        for ($i = 0; $i < $length; $i++) {
            $digit = (int) $number[$length - 1 - $i];

            if ($i % 2 === 1) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }

            $sum += $digit;
        }

        return $sum % 10 === 0;
    }
}
```

## 関連

- [StringValue API](/api/string/string-value)
- [カスタム数値](/extension/custom-number)

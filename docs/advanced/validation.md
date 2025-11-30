# バリデーション戦略

複雑なバリデーションロジックの実装方法を解説します。

## 単一フィールドのバリデーション

### 基本パターン

```php
$result = EmailAddress::tryFrom($input);

if ($result->isErr()) {
    $error = $result->unwrapErr();
    // エラー処理
}
```

### エラーメッセージの取得

```php
$result = EmailAddress::tryFrom($input);

if ($result->isErr()) {
    $error = $result->unwrapErr();

    $code = $error->getCode();       // エラーコード
    $message = $error->getMessage(); // エラーメッセージ
    $details = $error->getDetails(); // 詳細情報
}
```

## 複数フィールドのバリデーション

### 順次バリデーション

最初のエラーで停止するパターン。

```php
function validateUser(array $data): Result
{
    return EmailAddress::tryFrom($data['email'] ?? '')
        ->andThen(fn($email) =>
            Age::tryFrom($data['age'] ?? 0)
                ->map(fn($age) => compact('email', 'age'))
        )
        ->andThen(fn($result) =>
            Username::tryFrom($data['username'] ?? '')
                ->map(fn($username) => [...$result, 'username' => $username])
        );
}
```

### 全エラー収集

すべてのフィールドをバリデーションしてエラーを収集するパターン。

```php
function validateAllFields(array $data): array
{
    $errors = [];
    $values = [];

    // メール
    $emailResult = EmailAddress::tryFrom($data['email'] ?? '');
    if ($emailResult->isErr()) {
        $errors['email'] = $emailResult->unwrapErr()->getMessage();
    } else {
        $values['email'] = $emailResult->unwrap();
    }

    // 年齢
    $ageResult = Age::tryFrom($data['age'] ?? 0);
    if ($ageResult->isErr()) {
        $errors['age'] = $ageResult->unwrapErr()->getMessage();
    } else {
        $values['age'] = $ageResult->unwrap();
    }

    // ユーザー名
    $usernameResult = Username::tryFrom($data['username'] ?? '');
    if ($usernameResult->isErr()) {
        $errors['username'] = $usernameResult->unwrapErr()->getMessage();
    } else {
        $values['username'] = $usernameResult->unwrap();
    }

    return ['errors' => $errors, 'values' => $values];
}
```

## 条件付きバリデーション

### フィールド間の依存関係

```php
function validatePayment(array $data): Result
{
    $methodResult = PaymentMethod::tryFrom($data['method'] ?? '');

    if ($methodResult->isErr()) {
        return $methodResult;
    }

    $method = $methodResult->unwrap();

    // 支払い方法に応じたバリデーション
    return match ($method) {
        PaymentMethod::CREDIT_CARD => validateCreditCard($data),
        PaymentMethod::BANK_TRANSFER => validateBankTransfer($data),
        PaymentMethod::CASH_ON_DELIVERY => ok($data),
    };
}

function validateCreditCard(array $data): Result
{
    return CreditCardNumber::tryFrom($data['card_number'] ?? '')
        ->andThen(fn($cardNumber) =>
            ExpirationDate::tryFrom($data['expiration'] ?? '')
                ->map(fn($expiration) => compact('cardNumber', 'expiration'))
        )
        ->andThen(fn($result) =>
            SecurityCode::tryFrom($data['cvv'] ?? '')
                ->map(fn($cvv) => [...$result, 'cvv' => $cvv])
        );
}
```

### 任意フィールド

```php
function validateProfile(array $data): Result
{
    return Username::tryFrom($data['username'] ?? '')
        ->map(fn($username) => ['username' => $username])
        ->andThen(function($result) use ($data) {
            // 電話番号は任意
            if (empty($data['phone'])) {
                return ok([...$result, 'phone' => null]);
            }

            return PhoneNumber::tryFrom($data['phone'])
                ->map(fn($phone) => [...$result, 'phone' => $phone]);
        });
}
```

## カスタムバリデーション

### isValid のオーバーライド

```php
use WizDevelop\PhpMonad\Result;
use WizDevelop\PhpValueObject\Error\ValueObjectError;

#[ValueObjectMeta(name: 'パスワード')]
final readonly class Password extends StringValue
{
    #[Override]
    public static function minLength(): int
    {
        return 8;
    }

    #[Override]
    public static function maxLength(): int
    {
        return 100;
    }

    #[Override]
    protected static function isValid(string $value): Result
    {
        // 大文字を含む
        if (!preg_match('/[A-Z]/', $value)) {
            return Result\err(ValueObjectError::string()->invalidRegex(
                className: static::class,
                regex: '大文字を含む必要があります',
                value: $value,
            ));
        }

        // 小文字を含む
        if (!preg_match('/[a-z]/', $value)) {
            return Result\err(ValueObjectError::string()->invalidRegex(
                className: static::class,
                regex: '小文字を含む必要があります',
                value: $value,
            ));
        }

        // 数字を含む
        if (!preg_match('/[0-9]/', $value)) {
            return Result\err(ValueObjectError::string()->invalidRegex(
                className: static::class,
                regex: '数字を含む必要があります',
                value: $value,
            ));
        }

        return Result\ok(true);
    }
}
```

### 複合値オブジェクトのバリデーション

```php
final readonly class DateRange
{
    private function __construct(
        public LocalDate $start,
        public LocalDate $end,
    ) {
    }

    public static function tryFrom(
        LocalDate $start,
        LocalDate $end
    ): Result {
        if ($start->isAfter($end)) {
            return Result\err(ValueObjectError::dateTime()->invalidRange(
                className: static::class,
                message: '開始日は終了日より前である必要があります',
            ));
        }

        return Result\ok(new self($start, $end));
    }
}
```

## バリデーションヘルパー

### バリデータクラス

```php
class UserValidator
{
    public function validate(array $data): ValidationResult
    {
        $errors = [];
        $values = [];

        $this->validateField(
            'email',
            fn() => EmailAddress::tryFrom($data['email'] ?? ''),
            $errors,
            $values
        );

        $this->validateField(
            'age',
            fn() => Age::tryFrom($data['age'] ?? 0),
            $errors,
            $values
        );

        return new ValidationResult($errors, $values);
    }

    private function validateField(
        string $field,
        callable $validator,
        array &$errors,
        array &$values
    ): void {
        $result = $validator();

        if ($result->isErr()) {
            $errors[$field] = $result->unwrapErr()->getMessage();
        } else {
            $values[$field] = $result->unwrap();
        }
    }
}

class ValidationResult
{
    public function __construct(
        public array $errors,
        public array $values,
    ) {
    }

    public function isValid(): bool
    {
        return empty($this->errors);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getValues(): array
    {
        return $this->values;
    }
}
```

## 関連

- [Result 型との連携](/advanced/result-type)
- [カスタム文字列](/extension/custom-string)

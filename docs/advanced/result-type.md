# Result 型との連携

PHP Value Object ライブラリは [php-monad](https://github.com/wiz-develop/php-monad) ライブラリの Result 型と密接に連携しています。

## Result 型の基本

Result 型は成功 (Ok) または失敗 (Err) を表現する型です。例外を使わずにエラーを扱えます。

```php
use WizDevelop\PhpMonad\Result;
use function WizDevelop\PhpMonad\Result\{ok, err};

// 成功
$success = ok("value");

// 失敗
$failure = err(new Error("message"));
```

## tryFrom メソッド

値オブジェクトの `tryFrom` メソッドは Result 型を返します。

```php
use WizDevelop\PhpValueObject\String\EmailAddress;

$result = EmailAddress::tryFrom($userInput);

if ($result->isOk()) {
    $email = $result->unwrap();
} else {
    $error = $result->unwrapErr();
    echo $error->getMessage();
}
```

## チェーン処理

### andThen

成功時に次の処理を実行します。

```php
$result = EmailAddress::tryFrom($input)
    ->andThen(fn($email) => validateDomain($email));
```

### map

成功時に値を変換します。

```php
$result = EmailAddress::tryFrom($input)
    ->map(fn($email) => $email->value);
// Result<string, ValueObjectError>
```

### mapErr

失敗時にエラーを変換します。

```php
$result = EmailAddress::tryFrom($input)
    ->mapErr(fn($error) => new CustomError($error->getMessage()));
```

### orElse

失敗時に別の処理を試みます。

```php
$result = EmailAddress::tryFrom($primaryEmail)
    ->orElse(fn() => EmailAddress::tryFrom($fallbackEmail));
```

## match パターン

成功時と失敗時の処理を一度に定義できます。

```php
$email = EmailAddress::tryFrom($input)->match(
    ok: fn($email) => $email,
    err: fn($error) => throw new ValidationException($error->getMessage())
);
```

## 複数の Result を組み合わせる

### 順次処理

```php
function createUser(array $data): Result
{
    return EmailAddress::tryFrom($data['email'])
        ->andThen(fn($email) =>
            Password::tryFrom($data['password'])
                ->map(fn($password) => ['email' => $email, 'password' => $password])
        )
        ->andThen(fn($validated) =>
            Username::tryFrom($data['username'])
                ->map(fn($username) => [...$validated, 'username' => $username])
        )
        ->map(fn($all) => new User($all['email'], $all['password'], $all['username']));
}
```

### 並列処理

すべての Result が成功した場合のみ続行します。

```php
function validateForm(array $data): Result
{
    $emailResult = EmailAddress::tryFrom($data['email']);
    $nameResult = StringValue::tryFrom($data['name']);
    $ageResult = Age::tryFrom($data['age']);

    // すべて成功した場合のみ
    if ($emailResult->isOk() && $nameResult->isOk() && $ageResult->isOk()) {
        return ok([
            'email' => $emailResult->unwrap(),
            'name' => $nameResult->unwrap(),
            'age' => $ageResult->unwrap(),
        ]);
    }

    // 最初のエラーを返す
    return match (true) {
        $emailResult->isErr() => $emailResult,
        $nameResult->isErr() => $nameResult,
        default => $ageResult,
    };
}
```

## ArrayList::tryFromResults

複数の Result から ArrayList を作成します。

```php
$results = [
    EmailAddress::tryFrom('a@example.com'),
    EmailAddress::tryFrom('b@example.com'),
    EmailAddress::tryFrom('invalid'),
];

$listResult = ArrayList::tryFromResults($results);
// Err (3番目が失敗しているため)
```

## 実践的な例

### フォームバリデーション

```php
class UserRegistrationForm
{
    public static function validate(array $data): Result
    {
        return self::validateEmail($data['email'] ?? '')
            ->andThen(fn($email) =>
                self::validatePassword($data['password'] ?? '')
                    ->map(fn($password) => ['email' => $email, 'password' => $password])
            )
            ->andThen(fn($result) =>
                self::validatePasswordConfirmation(
                    $data['password'] ?? '',
                    $data['password_confirmation'] ?? ''
                )->map(fn() => $result)
            )
            ->map(fn($result) => new UserRegistrationData(
                $result['email'],
                $result['password']
            ));
    }

    private static function validateEmail(string $value): Result
    {
        return EmailAddress::tryFrom($value);
    }

    private static function validatePassword(string $value): Result
    {
        return Password::tryFrom($value);
    }

    private static function validatePasswordConfirmation(
        string $password,
        string $confirmation
    ): Result {
        if ($password !== $confirmation) {
            return err(ValueObjectError::general()->custom(
                'パスワードが一致しません'
            ));
        }
        return ok(true);
    }
}

// 使用例
$result = UserRegistrationForm::validate($request->all());

$result->match(
    ok: fn($data) => $userService->register($data),
    err: fn($error) => redirect()->back()->withErrors($error->getMessage())
);
```

### API レスポンスの処理

```php
class ApiClient
{
    public function fetchUser(int $id): Result
    {
        $response = $this->http->get("/users/{$id}");

        if ($response->failed()) {
            return err(ValueObjectError::general()->custom(
                'API request failed: ' . $response->status()
            ));
        }

        $data = $response->json();

        return UserId::tryFrom($data['id'])
            ->andThen(fn($id) =>
                EmailAddress::tryFrom($data['email'])
                    ->map(fn($email) => ['id' => $id, 'email' => $email])
            )
            ->andThen(fn($result) =>
                Username::tryFrom($data['username'])
                    ->map(fn($username) => [...$result, 'username' => $username])
            )
            ->map(fn($result) => new UserDTO(
                $result['id'],
                $result['email'],
                $result['username']
            ));
    }
}
```

## Option 型との連携

`fromNullable` メソッドは Option 型を返します。

```php
use WizDevelop\PhpMonad\Option;

$option = EmailAddress::fromNullable($maybeNull);

// Some の場合のみ処理
$option->map(fn($email) => sendEmail($email));

// デフォルト値
$email = $option->unwrapOr(EmailAddress::from("default@example.com"));

// null に変換
$emailOrNull = $option->unwrapOrNull();
```

## 関連

- [コンセプト - Result 型によるエラーハンドリング](/guide/concepts#result-型によるエラーハンドリング)
- [php-monad ドキュメント](https://github.com/wiz-develop/php-monad)

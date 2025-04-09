## セキュリティ

### 機密ファイル

絶対に読んだり変更したりしないでください：

-   .env files
-   \*_/config/secrets._
-   \*_/_.pem
- APIキー、トークン、認証情報を含むファイル

### セキュリティの実践

- 機密ファイルをコミットしない
- 秘密には環境変数を使う
- 認証情報をログや出力に残さない


## 重要

ユーザーは日本人です。常に日本語で会話してください。
ユーザーはClineよりプログラミングが得意ですが、時短のためにClineにコーディングを依頼しています。

2回以上連続でテストを失敗した時は、現在の状況を整理して、一緒に解決方法を考えます。

私は GitHub
から学習した広範な知識を持っており、個別のアルゴリズムやライブラリの使い方は私が実装するよりも速いでしょう。テストコードを書いて動作確認しながら、ユーザーに説明しながらコードを書きます。

反面、現在のコンテキストに応じた処理は苦手です。コンテキストが不明瞭な時は、ユーザーに確認します。

## 作業開始準備

`git status` で現在の git のコンテキストを確認します。
もし指示された内容と無関係な変更が多い場合、現在の変更からユーザーに別のタスクとして開始するように提案してください。

無視するように言われた場合は、そのまま続行します。


# コーディングプラクティス

## 原則

### 関数型アプローチ (FP)

- 純粋関数を優先
- 不変データ構造を使用
- 副作用を分離
- 型安全性を確保

### ドメイン駆動設計 (DDD)

- 値オブジェクトとエンティティを区別
- 集約で整合性を保証
- リポジトリでデータアクセスを抽象化
- 境界付けられたコンテキストを意識

### テスト駆動開発 (TDD)

- Red-Green-Refactorサイクル
- テストを仕様として扱う
- 小さな単位で反復
- 継続的なリファクタリング

## 実装パターン

### 型定義

```php
// 厳密な型を定義
declare(strict_types=1);

// 値オブジェクトとして使用する型
final readonly class Money
{
    private function __construct(
        private float $amount
    ) {
        if ($amount < 0) {
            throw new InvalidArgumentException('金額は0以上である必要があります');
        }
    }

    public static function fromFloat(float $amount): self
    {
        return new self($amount);
    }

    public function getAmount(): float
    {
        return $this->amount;
    }
}

final readonly class Email
{
    private function __construct(
        private string $value
    ) {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('不正なメールアドレス形式です');
        }
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function toString(): string
    {
        return $this->value;
    }
}
```

### 値オブジェクト

- 不変
- 値に基づく同一性
- 自己検証
- ドメイン操作を持つ

```php
// 作成関数はバリデーション付き
function createMoney(float $amount): Result
{
    try {
        return Result::ok(Money::fromFloat($amount));
    } catch (InvalidArgumentException $e) {
        return Result::err($e->getMessage());
    }
}
```

### エンティティ

- IDに基づく同一性
- 制御された更新
- 整合性ルールを持つ

### Result型

```php
final readonly class Result
{
    private function __construct(
        private mixed $value,
        private ?string $error = null
    ) {}

    public static function ok(mixed $value): self
    {
        return new self($value);
    }

    public static function err(string $error): self
    {
        return new self(null, $error);
    }

    public function isOk(): bool
    {
        return $this->error === null;
    }

    public function getValue(): mixed
    {
        if (!$this->isOk()) {
            throw new RuntimeException($this->error);
        }
        return $this->value;
    }

    public function getError(): ?string
    {
        return $this->error;
    }
}
```

- 成功/失敗を明示
- 早期リターンパターンを使用
- エラー型を定義

### リポジトリ

- ドメインモデルのみを扱う
- 永続化の詳細を隠蔽
- テスト用のインメモリ実装を提供

### アダプターパターン

- 外部依存を抽象化
- インターフェースは呼び出し側で定義
- テスト時は容易に差し替え可能

## 実装手順

1. **型設計**
   - まず型を定義
   - ドメインの言語を型で表現

2. **純粋関数から実装**
   - 外部依存のない関数を先に
   - テストを先に書く

3. **副作用を分離**
   - IO操作は関数の境界に押し出す
   - 副作用を持つ処理を非同期処理でラップ

4. **アダプター実装**
   - 外部サービスやDBへのアクセスを抽象化
   - テスト用モックを用意

## プラクティス

- 小さく始めて段階的に拡張
- 過度な抽象化を避ける
- コードよりも型を重視
- 複雑さに応じてアプローチを調整

## コードスタイル

- 関数優先（クラスは必要な場合のみ）
- 不変更新パターンの活用
- 早期リターンで条件分岐をフラット化
- エラーとユースケースの列挙型定義

## テスト戦略

- 純粋関数の単体テストを優先
- インメモリ実装によるリポジトリテスト
- テスト可能性を設計に組み込む
- アサートファースト：期待結果から逆算

## テストの書き方

PHPUnitを使用してテストを書きます。

```php
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    public function test_正の金額でMoneyが作成できる(): void
    {
        // Arrange - Act
        $money = Money::fromFloat(100.0);

        // Assert
        $this->assertSame(100.0, $money->getAmount());
    }

    public function test_負の金額でMoneyを作成するとエラーになる(): void
    {
        // Assert
        $this->expectException(InvalidArgumentException::class);

        // Act
        Money::fromFloat(-100.0);
    }
}
```

アサーションの書き方：
- テストメソッド名は日本語で`test_状況_操作_期待結果`の形式
- アサーションメッセージは期待する動作を明確に

## コード品質の監視

### カバレッジ

カバレッジの取得には以下のコマンドを使用：

```bash
./vendor/bin/phpunit --coverage-html coverage
```

実行コードと純粋な関数を分離することで、高いカバレッジを維持する：

- 実装（lib.php）: ロジックを純粋な関数として実装
- エクスポート（index.php）: 外部向けインターフェースの定義
- 実行（cli.php）: エントリーポイントとデバッグコード

### 静的解析

- PHP_CodeSniffer: コーディング規約チェック
- PHPStan: 静的解析（Level 8を目指す）
- PHP CS Fixer: コード整形

### デッドコード解析

- PHP Dead Code Detector (PDCD)を使用
- 未使用のuse分やメソッドや関数を定期的に確認し削除

# テスト駆動開発 (TDD) の基本

## 基本概念

テスト駆動開発（TDD）は以下のサイクルで進める開発手法です：

1. **Red**: まず失敗するテストを書く
2. **Green**: テストが通るように最小限の実装をする
3. **Refactor**: コードをリファクタリングして改善する

## 重要な考え方

- **テストは仕様である**: テストコードは実装の仕様を表現したもの
- **Assert-Act-Arrange の順序で考える**:
  1. まず期待する結果（アサーション）を定義
  2. 次に操作（テスト対象の処理）を定義
  3. 最後に準備（テスト環境のセットアップ）を定義
- **テスト名は「状況→操作→結果」の形式で記述**: 例:
  「有効なトークンの場合にユーザー情報を取得すると成功すること」

## リファクタリングフェーズの重要ツール

テストが通った後のリファクタリングフェーズでは、以下のツールを活用します：

1. **静的解析**:
   - `./vendor/bin/phpstan analyse`
   - `./vendor/bin/phpcs`

2. **コード整形**:
   - `./vendor/bin/php-cs-fixer fix`

3. **コードカバレッジ測定**:
   - `./vendor/bin/phpunit --coverage-html coverage`

4. **Gitによるバージョン管理**:
   - 各フェーズ（テスト作成→実装→リファクタリング）の完了時にコミット
   - タスク完了時にはユーザーに確認：
     ```bash
     git status  # 変更状態を確認
     git add <関連ファイル>
     git commit -m "<適切なコミットメッセージ>"
     ```
   - コミットメッセージはプレフィックスを使用：
     - `test:` - テストの追加・修正
     - `feat:` - 新機能の実装
     - `refactor:` - リファクタリング

## 詳細情報

Deno環境におけるTDDの詳細な実践方法、例、各種ツールの活用方法については、以下のファイルを参照してください：

```
.cline/roomodes/php-tdd.md
```

このファイルにはテストファーストモードの詳細な手順、テストの命名規約、リファクタリングのベストプラクティスなどが含まれています。


## PHP

PHPでのコーディングにおける一般的なベストプラクティスをまとめます。

### 方針

- 最初に型と、それを処理する関数のインターフェースを考える
- コードのコメントとして、そのファイルがどういう仕様化を可能な限り明記する
- 実装が内部状態を持たないとき、 class による実装を避けて関数を優先する
- 副作用を抽象するために、アダプタパターンで外部依存を抽象し、テストではインメモリなアダプタで処理する

### 型の使用方針

1. 具体的な型を使用
   - mixed の使用を避ける
   - 型を明示的に宣言する
   - Union型とIntersection型を活用する

2. 型の命名
   - 意味のある名前をつける
   - 型の意図を明確にする
   ```php
   // Good
   final readonly class UserId
   {
       private function __construct(
           private string $value
       ) {}

       public static function fromString(string $value): self
       {
           return new self($value);
       }
   }

   final readonly class UserData
   {
       public function __construct(
           private UserId $id,
           private DateTimeImmutable $createdAt
       ) {}
   }

   // Bad
   class Data
   {
       public mixed $value;
   }
   ```

### エラー処理

1. Result型の使用
   ```php
   enum ApiErrorType: string
   {
       case NETWORK = 'network';
       case NOT_FOUND = 'not_found';
       case UNAUTHORIZED = 'unauthorized';
   }

   interface ApiError
   {
       public function getType(): ApiErrorType;
       public function getMessage(): string;
   }

   final readonly class NetworkError implements ApiError
   {
       public function __construct(
           private string $message
       ) {}

       public function getType(): ApiErrorType
       {
           return ApiErrorType::NETWORK;
       }

       public function getMessage(): string
       {
           return $this->message;
       }
   }

   final readonly class NotFoundError implements ApiError
   {
       public function __construct(
           private string $message
       ) {}

       public function getType(): ApiErrorType
       {
           return ApiErrorType::NOT_FOUND;
       }

       public function getMessage(): string
       {
           return $this->message;
       }
   }

   final readonly class UnauthorizedError implements ApiError
   {
       public function __construct(
           private string $message
       ) {}

       public function getType(): ApiErrorType
       {
           return ApiErrorType::UNAUTHORIZED;
       }

       public function getMessage(): string
       {
           return $this->message;
       }
   }

   final readonly class Result
   {
       private function __construct(
           private mixed $value = null,
           private ?ApiError $error = null
       ) {}

       public static function ok(mixed $value): self
       {
           return new self($value);
       }

       public static function err(ApiError $error): self
       {
           return new self(null, $error);
       }
   }

   final class UserRepository
   {
       public function fetchUser(string $id): Result
       {
           try {
               $response = $this->httpClient->get("/users/{$id}");
               if ($response->getStatusCode() !== 200) {
                   return match ($response->getStatusCode()) {
                       404 => Result::err(new NotFoundError('User not found')),
                       401 => Result::err(new UnauthorizedError('Unauthorized')),
                       default => Result::err(new NetworkError(
                           "HTTP error: {$response->getStatusCode()}"
                       )),
                   };
               }
               return Result::ok($response->toArray());
           } catch (Exception $error) {
               return Result::err(new NetworkError($error->getMessage()));
           }
       }
   }
   ```

2. エラー型の定義
   - 具体的なケースを列挙
   - エラーメッセージを含める
   - 型の網羅性を確保

### 実装パターン

1. 関数ベース（状態を持たない場合）
   ```php
   interface LoggerInterface
   {
       public function log(string $message): void;
   }

   final readonly class Logger implements LoggerInterface
   {
       public function log(string $message): void
       {
           echo sprintf("[%s] %s\n",
               (new DateTimeImmutable())->format(DATE_ATOM),
               $message
           );
       }
   }

   function createLogger(): LoggerInterface
   {
       return new Logger();
   }
   ```

2. クラスベース（状態を持つ場合）
   ```php
   interface CacheInterface
   {
       public function get(string $key): mixed;
       public function set(string $key, mixed $value): void;
   }

   final readonly class TimeBasedCache implements CacheInterface
   {
       private array $items = [];

       public function __construct(
           private int $ttlMs
       ) {}

       public function get(string $key): mixed
       {
           $item = $this->items[$key] ?? null;
           if (!$item || time() * 1000 > $item['expireAt']) {
               return null;
           }
           return $item['value'];
       }

       public function set(string $key, mixed $value): void
       {
           $this->items[$key] = [
               'value' => $value,
               'expireAt' => time() * 1000 + $this->ttlMs,
           ];
       }
   }
   ```

3. Adapterパターン（外部依存の抽象化）
   ```php
   interface HttpClientInterface
   {
       public function get(string $path): Result;
   }

   final readonly class HttpClient implements HttpClientInterface
   {
       public function __construct(
           private array $headers
       ) {}

       public function get(string $path): Result
       {
           try {
               $response = $this->sendRequest('GET', $path);
               if ($response->getStatusCode() !== 200) {
                   return Result::err(new NetworkError(
                       "HTTP error: {$response->getStatusCode()}"
                   ));
               }
               return Result::ok($response->toArray());
           } catch (Exception $error) {
               return Result::err(new NetworkError($error->getMessage()));
           }
       }
   }

  ;// 利用
   final readonly class ApiClient
   {
       public function __construct(
           private HttpClientInterface $httpClient,
           private string $baseUrl
       ) {}

       public function getUser(string $id): Result
       {
           return $this->httpClient->get("{$this->baseUrl}/users/{$id}");
       }
   }
   ```

### 実装の選択基準

1. 関数を選ぶ場合
   - 単純な操作のみ
   - 内部状態が不要
   - 依存が少ない
   - テストが容易

2. classを選ぶ場合
   - 内部状態の管理が必要
   - 設定やリソースの保持が必要
   - メソッド間で状態を共有
   - ライフサイクル管理が必要

3. Adapterを選ぶ場合
   - 外部依存の抽象化
   - テスト時のモック化が必要
   - 実装の詳細を隠蔽したい
   - 差し替え可能性を確保したい

### 一般的なルール

1. 依存性の注入
   - 外部依存はコンストラクタで注入
   - テスト時にモックに置き換え可能に
   - グローバルな状態を避ける

2. インターフェースの設計
   - 必要最小限のメソッドを定義
   - 実装の詳細を含めない
   - フレームワーク固有の型を避ける

3. テスト容易性
   - モックの実装を簡潔に
   - エッジケースのテストを含める
   - テストヘルパーを適切に分離

4. コードの分割
   - 単一責任の原則に従う
   - 適切な粒度でモジュール化
   - 循環参照を避ける
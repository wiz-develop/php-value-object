# カスタムコレクション値オブジェクト

ArrayList や Map を継承してカスタムコレクション値オブジェクトを作成する方法を解説します。

## 基本的な拡張

### 型制約付きリスト

特定の型のみを許容するリストを作成できます。

```php
use WizDevelop\PhpValueObject\Collection\ArrayList;
use WizDevelop\PhpValueObject\String\EmailAddress;

/**
 * メールアドレスのリスト
 * @extends ArrayList<EmailAddress>
 */
final readonly class EmailAddressList extends ArrayList
{
    /**
     * メールアドレスの配列から作成
     */
    public static function fromStrings(array $emails): self
    {
        $addresses = array_map(
            fn($email) => EmailAddress::from($email),
            $emails
        );

        return self::make($addresses);
    }

    /**
     * バリデーション付きで作成
     */
    public static function tryFromStrings(array $emails): Result
    {
        $results = array_map(
            fn($email) => EmailAddress::tryFrom($email),
            $emails
        );

        return self::tryFromResults($results);
    }

    /**
     * カンマ区切りの文字列として出力
     */
    public function toCommaSeparated(): string
    {
        return implode(', ', array_map(
            fn($email) => $email->value,
            $this->toArray()
        ));
    }
}
```

### 使用例

```php
$list = EmailAddressList::fromStrings([
    'a@example.com',
    'b@example.com',
    'c@example.com',
]);

echo $list->toCommaSeparated();
// a@example.com, b@example.com, c@example.com
```

### 要素数制限付きリスト

```php
use WizDevelop\PhpMonad\Result;
use WizDevelop\PhpValueObject\Error\ValueObjectError;

/**
 * 最大10件のタグリスト
 * @extends ArrayList<StringValue>
 */
final readonly class TagList extends ArrayList
{
    private const int MAX_TAGS = 10;

    public static function from(mixed ...$items): static
    {
        if (count($items) > self::MAX_TAGS) {
            throw new \InvalidArgumentException(
                'タグは最大' . self::MAX_TAGS . '件までです'
            );
        }

        return parent::from(...$items);
    }

    public static function tryFrom(mixed ...$items): Result
    {
        if (count($items) > self::MAX_TAGS) {
            return Result\err(ValueObjectError::collection()->tooMany(
                className: static::class,
                max: self::MAX_TAGS,
                actual: count($items),
            ));
        }

        return parent::tryFrom(...$items);
    }

    /**
     * タグを追加 (上限チェック付き)
     */
    public function addTag(StringValue $tag): Result
    {
        if ($this->count() >= self::MAX_TAGS) {
            return Result\err(ValueObjectError::collection()->tooMany(
                className: static::class,
                max: self::MAX_TAGS,
                actual: $this->count() + 1,
            ));
        }

        // 重複チェック
        if ($this->contains($tag->value)) {
            return Result\ok($this);
        }

        return Result\ok($this->push($tag));
    }
}
```

## Map の拡張

### 設定値のマップ

```php
/**
 * アプリケーション設定
 * @extends Map<string, mixed>
 */
final readonly class Config extends Map
{
    /**
     * 環境変数から作成
     */
    public static function fromEnv(array $keys): self
    {
        $pairs = [];
        foreach ($keys as $key) {
            $value = getenv($key);
            if ($value !== false) {
                $pairs[] = new Pair($key, $value);
            }
        }

        return self::from(...$pairs);
    }

    /**
     * 設定値を取得 (デフォルト値付き)
     */
    public function getOrDefault(string $key, mixed $default): mixed
    {
        return $this->has($key) ? $this->get($key) : $default;
    }

    /**
     * 整数として取得
     */
    public function getInt(string $key, int $default = 0): int
    {
        $value = $this->getOrDefault($key, $default);
        return (int) $value;
    }

    /**
     * ブール値として取得
     */
    public function getBool(string $key, bool $default = false): bool
    {
        $value = $this->getOrDefault($key, $default);
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}
```

### 翻訳辞書

```php
/**
 * 翻訳辞書
 * @extends Map<string, string>
 */
final readonly class TranslationDictionary extends Map
{
    /**
     * JSON ファイルから作成
     */
    public static function fromJsonFile(string $path): self
    {
        $content = file_get_contents($path);
        $data = json_decode($content, true);

        return self::make($data);
    }

    /**
     * 翻訳を取得 (見つからない場合はキーを返す)
     */
    public function translate(string $key): string
    {
        return $this->has($key) ? $this->get($key) : $key;
    }

    /**
     * プレースホルダーを置換
     */
    public function translateWithParams(string $key, array $params): string
    {
        $text = $this->translate($key);

        foreach ($params as $name => $value) {
            $text = str_replace(':' . $name, $value, $text);
        }

        return $text;
    }
}
```

## ValueObjectList の拡張

### ユーザー ID リスト

```php
use WizDevelop\PhpValueObject\ValueObjectList;

/**
 * ユーザー ID のリスト
 * @extends ValueObjectList<UserId>
 */
final readonly class UserIdList extends ValueObjectList
{
    /**
     * 整数配列から作成
     */
    public static function fromIntegers(array $ids): self
    {
        $userIds = array_map(
            fn($id) => UserId::from($id),
            $ids
        );

        return new self($userIds);
    }

    /**
     * 整数配列として出力
     */
    public function toIntegers(): array
    {
        return array_map(
            fn($userId) => $userId->value,
            $this->toArray()
        );
    }

    /**
     * IN句用のプレースホルダー文字列
     */
    public function toPlaceholders(): string
    {
        return implode(', ', array_fill(0, $this->count(), '?'));
    }
}
```

## 複合コレクション

### 注文明細リスト

```php
/**
 * 注文明細
 */
final readonly class OrderItem
{
    public function __construct(
        public ProductCode $productCode,
        public Quantity $quantity,
        public Price $unitPrice,
    ) {
    }

    public function subtotal(): Price
    {
        return Price::from(
            $this->unitPrice->value * new Number($this->quantity->value)
        );
    }
}

/**
 * 注文明細リスト
 * @extends ArrayList<OrderItem>
 */
final readonly class OrderItemList extends ArrayList
{
    /**
     * 合計金額
     */
    public function total(): Price
    {
        return $this->reduce(
            fn(Price $carry, OrderItem $item) => $carry->add($item->subtotal()),
            Price::from(new Number("0"))
        );
    }

    /**
     * 合計数量
     */
    public function totalQuantity(): int
    {
        return $this->reduce(
            fn(int $carry, OrderItem $item) => $carry + $item->quantity->value,
            0
        );
    }

    /**
     * 商品コードで検索
     */
    public function findByProductCode(ProductCode $code): Option
    {
        return $this->first(
            fn(OrderItem $item) => $item->productCode->equals($code)
        );
    }
}
```

## 関連

- [ArrayList API](/api/collection/array-list)
- [Map API](/api/collection/map)
- [ValueObjectList API](/api/collection/value-object-list)

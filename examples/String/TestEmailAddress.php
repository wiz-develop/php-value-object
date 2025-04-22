<?php

declare(strict_types=1);

namespace WizDevelop\PhpValueObject\Examples\String;

use WizDevelop\PhpValueObject\String\EmailAddress;

// 実行例
// 有効なメールアドレスでのインスタンス作成
$result = EmailAddress::tryFrom('test@example.com');
if ($result->isOk()) {
    $email = $result->unwrap();
    echo "メールアドレス: {$email->value}\n";
    echo "ローカル部: {$email->getLocalPart()}\n";
    echo "ドメイン部: {$email->getDomain()}\n";
} else {
    echo 'エラー: ' . $result->unwrapErr()->getMessage() . "\n";
}

// 無効なメールアドレスでのエラーハンドリング
$invalidResult = EmailAddress::tryFrom('invalid-email');
if ($invalidResult->isOk()) {
    $email = $invalidResult->unwrap();
    echo "メールアドレス: {$email->value}\n";
} else {
    echo 'エラー: ' . $invalidResult->unwrapErr()->getMessage() . "\n";
}

// Nullable対応
$nullableResult = EmailAddress::tryFromNullable(null);
if ($nullableResult->isOk()) {
    $option = $nullableResult->unwrap();
    if ($option->isSome()) {
        $email = $option->unwrap();
        echo "メールアドレス: {$email->value}\n";
    } else {
        echo "メールアドレスは指定されていません\n";
    }
} else {
    echo 'エラー: ' . $nullableResult->unwrapErr()->getMessage() . "\n";
}

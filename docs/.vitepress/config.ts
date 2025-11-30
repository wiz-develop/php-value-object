import { defineConfig } from 'vitepress'

export default defineConfig({
  title: 'PHP Value Object',
  description: '不変で型安全な値オブジェクトライブラリ',
  lang: 'ja-JP',

  base: '/php-value-object/',

  cleanUrls: true,

  lastUpdated: true,

  head: [
    ['meta', { name: 'theme-color', content: '#8B5CF6' }],
    ['meta', { property: 'og:type', content: 'website' }],
    ['meta', { property: 'og:locale', content: 'ja_JP' }],
    ['meta', { property: 'og:title', content: 'PHP Value Object' }],
    ['meta', { property: 'og:site_name', content: 'PHP Value Object' }],
  ],

  markdown: {
    lineNumbers: true,
    theme: {
      light: 'github-light',
      dark: 'github-dark'
    }
  },

  themeConfig: {
    nav: [
      { text: 'ガイド', link: '/guide/' },
      { text: 'チュートリアル', link: '/tutorial/' },
      { text: 'API リファレンス', link: '/api/' },
      { text: '拡張ガイド', link: '/extension/' },
      {
        text: 'リンク',
        items: [
          { text: 'GitHub', link: 'https://github.com/wiz-develop/php-value-object' },
          { text: 'Packagist', link: 'https://packagist.org/packages/wiz-develop/php-value-object' },
          { text: 'php-monad', link: 'https://github.com/wiz-develop/php-monad' }
        ]
      }
    ],

    sidebar: {
      '/guide/': [
        {
          text: 'はじめに',
          items: [
            { text: '概要', link: '/guide/' },
            { text: 'インストール', link: '/guide/installation' },
            { text: 'コンセプト', link: '/guide/concepts' },
            { text: 'クイックスタート', link: '/guide/quick-start' }
          ]
        }
      ],
      '/tutorial/': [
        {
          text: 'チュートリアル',
          items: [
            { text: '概要', link: '/tutorial/' },
            { text: 'Boolean', link: '/tutorial/boolean' },
            { text: 'String', link: '/tutorial/string' },
            { text: 'Number', link: '/tutorial/number' },
            { text: 'DateTime', link: '/tutorial/datetime' },
            { text: 'Collection', link: '/tutorial/collection' },
            { text: 'Enum', link: '/tutorial/enum' }
          ]
        }
      ],
      '/api/': [
        {
          text: 'API リファレンス',
          items: [
            { text: '概要', link: '/api/' }
          ]
        },
        {
          text: 'Boolean',
          collapsed: false,
          items: [
            { text: 'BooleanValue', link: '/api/boolean/boolean-value' }
          ]
        },
        {
          text: 'String',
          collapsed: false,
          items: [
            { text: 'StringValue', link: '/api/string/string-value' },
            { text: 'EmailAddress', link: '/api/string/email-address' },
            { text: 'Ulid', link: '/api/string/ulid' }
          ]
        },
        {
          text: 'Number',
          collapsed: false,
          items: [
            { text: 'IntegerValue', link: '/api/number/integer-value' },
            { text: 'PositiveIntegerValue', link: '/api/number/positive-integer-value' },
            { text: 'NegativeIntegerValue', link: '/api/number/negative-integer-value' },
            { text: 'DecimalValue', link: '/api/number/decimal-value' },
            { text: 'PositiveDecimalValue', link: '/api/number/positive-decimal-value' },
            { text: 'NegativeDecimalValue', link: '/api/number/negative-decimal-value' }
          ]
        },
        {
          text: 'DateTime',
          collapsed: false,
          items: [
            { text: 'LocalDate', link: '/api/datetime/local-date' },
            { text: 'LocalTime', link: '/api/datetime/local-time' },
            { text: 'LocalDateTime', link: '/api/datetime/local-datetime' },
            { text: 'LocalDateRange', link: '/api/datetime/local-date-range' }
          ]
        },
        {
          text: 'Collection',
          collapsed: false,
          items: [
            { text: 'ArrayList', link: '/api/collection/array-list' },
            { text: 'Map', link: '/api/collection/map' },
            { text: 'Pair', link: '/api/collection/pair' },
            { text: 'ValueObjectList', link: '/api/collection/value-object-list' }
          ]
        },
        {
          text: 'Enum',
          collapsed: false,
          items: [
            { text: 'EnumValue', link: '/api/enum/enum-value' }
          ]
        }
      ],
      '/extension/': [
        {
          text: '拡張ガイド',
          items: [
            { text: '概要', link: '/extension/' },
            { text: 'カスタム文字列', link: '/extension/custom-string' },
            { text: 'カスタム数値', link: '/extension/custom-number' },
            { text: 'カスタム日時', link: '/extension/custom-datetime' },
            { text: 'カスタムコレクション', link: '/extension/custom-collection' }
          ]
        }
      ],
      '/advanced/': [
        {
          text: '高度な使い方',
          items: [
            { text: '概要', link: '/advanced/' },
            { text: 'Result 型との連携', link: '/advanced/result-type' },
            { text: 'バリデーション戦略', link: '/advanced/validation' },
            { text: 'シリアライゼーション', link: '/advanced/serialization' }
          ]
        }
      ]
    },

    socialLinks: [
      { icon: 'github', link: 'https://github.com/wiz-develop/php-value-object' }
    ],

    footer: {
      message: 'MIT ライセンスの下で公開されています。',
      copyright: 'Copyright © 2024 wiz-develop'
    },

    search: {
      provider: 'local',
      options: {
        translations: {
          button: {
            buttonText: '検索',
            buttonAriaLabel: '検索'
          },
          modal: {
            noResultsText: '見つかりませんでした',
            resetButtonTitle: 'リセット',
            footer: {
              selectText: '選択',
              navigateText: '移動',
              closeText: '閉じる'
            }
          }
        }
      }
    },

    docFooter: {
      prev: '前のページ',
      next: '次のページ'
    },

    outline: {
      label: '目次',
      level: [2, 3]
    },

    lastUpdated: {
      text: '最終更新',
      formatOptions: {
        dateStyle: 'medium',
        timeStyle: 'short'
      }
    },

    editLink: {
      pattern: 'https://github.com/wiz-develop/php-value-object/edit/main/docs/:path',
      text: 'このページを編集する'
    }
  }
})

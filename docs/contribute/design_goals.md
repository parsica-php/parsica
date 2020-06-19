---
title: Design Goals
sidebar_label: Design Goals
---

Parsica aims to be the mainstream choice for anyone to create parsers. We want to support all use cases. When parsing a short string, Parsica should be worth picking over regular expressions; when parsing an entire language, it should be worth picking over a handwritten imperative parser. The API should be self-evident, it should be easy to get it right and hard to get it wrong. 

Developers should not have to learn anything other than this library itself: no need to learn FP, category theory, parser theory, or even the internals of this libary. Under the hood, we use theoretical concepts. However, when adhering to these concepts would require exposing them to the developers, we will prefer a tradeoff that hides them. 

The same goes for performance: Parsica should be performant enough to be a viable choice, but for most use cases, developers should not have to worry about learning how to achieve greater performance.

Parsica puts great focus on composability. To achieve this, we use immutability and referential transparency — not for the sake of perfection, but because these help to achieve effortless composition.

Finally, it should be easy for third party library authors to publish their own parsers as Composer packages, which in turn can be composed by other users.
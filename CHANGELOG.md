# Parable PHP DI

## 0.3.2

_Changes_
- Add static analysis using psalm.

## 0.3.1

_Fixes_
- Small fix for the new php8 reflection types.

## 0.3.0

_Changes_
- Dropped support for php7, php8 only from now on.

## 0.2.6

_Changes_
- `ReflectionParameter::getClass()` is deprecated in php 8+, so reworked to use `ReflectionParameter::getType()` instead.

## 0.2.5

_Changes_
- `normalize()` now also trims the provided string of whitespace first.

## 0.2.4

_Changes_
- Remove `object` return type hinting, since it didn't actually help.
- Thanks to @dmvdbrugge, `dynamicReturnTypeMeta.json` has been replaced by the PhpStorm-native `.phpstorm.meta.php`. See PR #1.

## 0.2.3

_Changes_
- Scalar parameters can now also be handled, but _only if_ they are considered optional. This means _after_ any required parameters, and _with_ a default value.

## 0.2.2

_Changes_
- Define strict types.

## 0.2.1

_Bugfixes_

- Fixed bug where in some cases, an un-injectable parameter's type could not be established.

## 0.2.0

_Changes_
- `STORED_DEPENDENCIES` and `NEW_DEPENDENCIES` are now `USE_STORED_DEPENDENCIES` and `USE_NEW_DEPENDENCIES`, for clarity.

_Bugfixes_
- `clearRelationship()` had a bug where right-hand relationships weren't being cleared, leading to more class names being stored than necessary.

## 0.1.3

_Bugfixes_
- Calling `map()` will now also normalize the names provided.
- Fixed type hint for `$maps`.

## 0.1.2

_Changes_
- Removed obsolete doc block annotations for params.
- Added dynamic return type config.

## 0.1.1

_Changes_
- Added `map(string $requested, string $replacement)`. This way, you can set replacement instantiating names beforehand, which only get resolved once the original name is retrieved.

## 0.1.0

_Changes_
- First release.

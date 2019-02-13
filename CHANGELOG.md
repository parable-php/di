# Parable PHP DI

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

# AllCoin DynamoDB Access Pattern

## Asset
Get the assets by :
- name (equal)

| PK | SK |
| --- | --- |
| name |  |

## Pair
Get the pairs by :
- asset name (equal)
- createAt (asc/desc)

| PK | SK |
| --- | --- |
| name | createAt |

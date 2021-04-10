# AllCoin Entities

## Description
| Name  | Usage     |
| ---   | ---       |
| Asset | financial product |
| Pair  | like BTC/USD      |

## Structure

### Asset
````json
{
    "name": "string",
    "createdAt": "string",
    "updatedAt": "string",
    "deletedAt": "string",
    "pairs": "array"
}
````

### Pair
````json
{
    "name": "string",
    "bidPrice": "float",
    "askPrice": "float",
    "createdAt": "string",
    "updatedAt": "string",
    "deletedAt": "string"
}
````

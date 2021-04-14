# AllCoin Entities

## Description
| Name  | Usage     |
| ---   | ---       |
| Asset | financial product |
| AssetPair  | like BTC/USD      |

## Structure

### Asset
````json
{
    "name": "string",
    "createdAt": "string",
    "updatedAt": "string",
    "pairs": "array"
}
````

### AssetPair
````json
{
    "name": "string",
    "bidPrice": "float",
    "askPrice": "float",
    "createdAt": "string",
    "updatedAt": "string"
}
````

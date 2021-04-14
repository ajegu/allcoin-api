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
    "id": "string",
    "name": "string",
    "createdAt": "string",
    "updatedAt": "string",
    "pairs": "array"
}
````

### AssetPair

````json
{
    "id": "string",
    "name": "string",
    "bidPrice": "float",
    "askPrice": "float",
    "createdAt": "string",
    "updatedAt": "string"
}
````

### AssetPairPrice

````json
{
    "assetPairId": "string",
    "bidPrice": "float",
    "askPrice": "float",
    "createdAt": "string",
    "updatedAt": "string"
}
````

provider "aws" {
    profile = "default"
    region = "eu-west-3"
}

module "binance_price_sync" {
    source = "./binance-price-sync"

    app_name = var.app_name
    app_timezone = var.app_timezone
    log_channel = var.log_channel
    dynamodb_table_name = var.dynamodb_table_name
}

module "binance_price_analyzer" {
    source = "./binance-price-analyzer"

    app_name = var.app_name
    app_timezone = var.app_timezone
    log_channel = var.log_channel
    dynamodb_table_name = var.dynamodb_table_name
}

module "binance_order_analyzer" {
    source = "./binance-order-analyzer"

    app_name = var.app_name
    app_timezone = var.app_timezone
    log_channel = var.log_channel
    dynamodb_table_name = var.dynamodb_table_name
}

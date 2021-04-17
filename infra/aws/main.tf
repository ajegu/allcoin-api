provider "aws" {
    profile = "default"
    region = "eu-west-3"
}

module "binance_price" {
    source = "./binance-price"

    app_name = var.app_name
    app_timezone = var.app_timezone
    log_channel = var.log_channel
    dynamodb_table_name = var.dynamodb_table_name
    app_version = var.app_version
}

module "price_analyser" {
    source = "./price-analyzer"

    app_name = var.app_name
    app_timezone = var.app_timezone
    log_channel = var.log_channel
    dynamodb_table_name = var.dynamodb_table_name
    app_version = var.app_version
}

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
    AWS_SNS_TOPIC_PRICE_ANALYZER_ARN = module.binance_price_analyzer.AWS_SNS_TOPIC_PRICE_ANALYZER_ARN
    AWS_SNS_TOPIC_ORDER_ANALYZER_ARN = module.binance_order_analyzer.AWS_SNS_TOPIC_ORDER_ANALYZER_ARN
}

module "binance_price_analyzer" {
    source = "./binance-price-analyzer"

    app_name = var.app_name
    app_timezone = var.app_timezone
    log_channel = var.log_channel
    dynamodb_table_name = var.dynamodb_table_name
    AWS_SNS_TOPIC_ORDER_ANALYZER_ARN = module.binance_order_analyzer.AWS_SNS_TOPIC_ORDER_ANALYZER_ARN
}

module "binance_order_analyzer" {
    source = "./binance-order-analyzer"

    app_name = var.app_name
    app_timezone = var.app_timezone
    log_channel = var.log_channel
    dynamodb_table_name = var.dynamodb_table_name
    AWS_SNS_TOPIC_PRICE_ANALYZER_ARN = module.binance_price_analyzer.AWS_SNS_TOPIC_PRICE_ANALYZER_ARN
}

module "binance_order_buy" {
    source = "./binance-order-buy"
    app_name = var.app_name
    app_timezone = var.app_timezone
    log_channel = var.log_channel
    dynamodb_table_name = var.dynamodb_table_name
    AWS_SNS_TOPIC_PRICE_ANALYZER_ARN = module.binance_price_analyzer.AWS_SNS_TOPIC_PRICE_ANALYZER_ARN
    AWS_SNS_TOPIC_ORDER_ANALYZER_ARN = module.binance_order_analyzer.AWS_SNS_TOPIC_ORDER_ANALYZER_ARN
}

module "binance_order_sell" {
    source = "./binance-order-sell"
    app_name = var.app_name
    app_timezone = var.app_timezone
    log_channel = var.log_channel
    dynamodb_table_name = var.dynamodb_table_name
    AWS_SNS_TOPIC_PRICE_ANALYZER_ARN = module.binance_price_analyzer.AWS_SNS_TOPIC_PRICE_ANALYZER_ARN
    AWS_SNS_TOPIC_ORDER_ANALYZER_ARN = module.binance_order_analyzer.AWS_SNS_TOPIC_ORDER_ANALYZER_ARN
}

module "order_report" {
    source = "./order-report"
    app_name = var.app_name
    app_timezone = var.app_timezone
    log_channel = var.log_channel
    dynamodb_table_name = var.dynamodb_table_name
    AWS_SNS_TOPIC_PRICE_ANALYZER_ARN = module.binance_price_analyzer.AWS_SNS_TOPIC_PRICE_ANALYZER_ARN
    AWS_SNS_TOPIC_ORDER_ANALYZER_ARN = module.binance_order_analyzer.AWS_SNS_TOPIC_ORDER_ANALYZER_ARN
}


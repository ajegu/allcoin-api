resource "aws_lambda_function" "binance_order_buy" {
    function_name = "${var.app_name}-${var.lambda_name}Lambda"
    handler = "lambda/binance_order_buy.php"
    role = aws_iam_role.binance_order_buy.arn
    runtime = "provided.al2"
    layers = [
        "arn:aws:lambda:eu-west-3:209497400698:layer:php-80:8"
    ]
    environment {
        variables = {
            AWS_DDB_TABLE_NAME = var.dynamodb_table_name
            LOG_CHANNEL = var.log_channel
            APP_TIMEZONE = var.app_timezone
        }
    }
    s3_bucket = "allcoin-api-deployment"
    s3_key = "allcoin-api.zip"
    source_code_hash = base64sha256("allcoin")

    depends_on = [
        aws_iam_role.binance_order_buy
    ]

    timeout = 900
}

resource "aws_lambda_permission" "binance_order_buy" {
    statement_id = "AllowExecutionFromSNS"
    action = "lambda:InvokeFunction"
    function_name = aws_lambda_function.binance_order_buy.function_name
    principal = "sns.amazonaws.com"
    source_arn = var.binance_price_analyzer_topic_arn
}

resource "aws_sns_topic_subscription" "binance_order_buy" {
    endpoint = aws_lambda_function.binance_order_buy.arn
    protocol = "lambda"
    topic_arn = var.binance_price_analyzer_topic_arn
    filter_policy = <<EOF
{
    "event": ["price_up"]
}
EOF
}

resource "aws_lambda_function" "binance_price_sync" {
    function_name = "${var.app_name}-${var.lambda_name}Lambda"
    handler = "lambda/binance_price_sync.php"
    role = aws_iam_role.binance_price_sync.arn
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
        aws_iam_role.binance_price_sync,
        aws_cloudwatch_event_rule.binance_price_sync,
    ]

    timeout = 900
}

resource "aws_lambda_permission" "binance_price_sync" {
    action = "lambda:InvokeFunction"
    function_name = aws_lambda_function.binance_price_sync.function_name
    principal = "events.amazonaws.com"
    source_arn = aws_cloudwatch_event_rule.binance_price_sync.arn
}

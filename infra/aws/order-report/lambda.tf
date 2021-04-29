resource "aws_lambda_function" "order_report" {
    function_name = "${var.app_name}-${var.lambda_name}Lambda"
    handler = "lambda/order_report.php"
    role = aws_iam_role.order_report.arn
    runtime = "provided.al2"
    layers = [
        "arn:aws:lambda:eu-west-3:209497400698:layer:php-80:8"
    ]
    environment {
        variables = {
            AWS_DDB_TABLE_NAME = var.dynamodb_table_name
            LOG_CHANNEL = var.log_channel
            APP_TIMEZONE = var.app_timezone
            AWS_SNS_TOPIC_PRICE_ANALYZER_ARN = var.AWS_SNS_TOPIC_PRICE_ANALYZER_ARN
            AWS_SNS_TOPIC_ORDER_ANALYZER_ARN = var.AWS_SNS_TOPIC_ORDER_ANALYZER_ARN
        }
    }
    s3_bucket = "allcoin-api-deployment"
    s3_key = "allcoin-api.zip"
    source_code_hash = base64sha256("allcoin")

    depends_on = [
        aws_iam_role.order_report,
        aws_cloudwatch_event_rule.order_report,
    ]

    timeout = 900
}

resource "aws_lambda_permission" "order_report" {
    action = "lambda:InvokeFunction"
    function_name = aws_lambda_function.order_report.function_name
    principal = "events.amazonaws.com"
    source_arn = aws_cloudwatch_event_rule.order_report.arn
}

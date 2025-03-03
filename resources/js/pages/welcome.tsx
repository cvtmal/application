import React, { useEffect, useRef } from 'react';
import { Head, usePage } from '@inertiajs/react';
import { useForm } from '@inertiajs/react';
import { router } from '@inertiajs/react';
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Card, CardContent, CardHeader, CardTitle, CardFooter } from "@/components/ui/card";
import { Alert, AlertDescription } from "@/components/ui/alert";
import { ScrollArea } from "@/components/ui/scroll-area";

export default function Welcome({ chatHistory = [] }) {
    const { flash } = usePage().props;
    const messagesEndRef = useRef(null);
    const scrollAreaRef = useRef(null);

    const { data, setData, post, processing, errors, reset } = useForm({
        message: '',
    });

    const scrollToBottom = () => {
        if (messagesEndRef.current) {
            // Use setTimeout to ensure scrolling happens after render
            setTimeout(() => {
                messagesEndRef.current.scrollIntoView({
                    behavior: 'smooth',
                    block: 'end'
                });
            }, 100);
        }
    };

    useEffect(() => {
        scrollToBottom();
    }, [chatHistory]);

    useEffect(() => {
        const handleResize = () => {
            // Recalculate scroll position when window resizes
            scrollToBottom();
        };

        window.addEventListener('resize', handleResize);
        return () => window.removeEventListener('resize', handleResize);
    }, []);

    function handleSubmit(e) {
        e.preventDefault();

        post('/submit-message', {
            preserveScroll: true,
            onSuccess: () => {
                reset('message');
            },
        });
    }

    function clearChat() {
        router.get('/clear-chat');
    }

    return (
        <>
            <Head title="Chat with Damian">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
            </Head>
            <div className="min-h-screen w-full flex items-center justify-center bg-blue-50 p-4">
                <Card className="w-full max-w-2xl flex flex-col" style={{ height: 'calc(100vh - 2rem)' }}>
                    <CardHeader className="border-b flex flex-row justify-between items-center p-4 shrink-0">
                        <CardTitle className="text-xl font-semibold">Chat with Damian's AI Assistant</CardTitle>
                        {chatHistory.length > 0 && (
                            <Button
                                variant="outline"
                                size="sm"
                                onClick={clearChat}
                                disabled={processing}
                            >
                                Clear Chat
                            </Button>
                        )}
                    </CardHeader>

                    {/* Messages */}
                    <div className="flex-grow overflow-hidden">
                        <ScrollArea
                            ref={scrollAreaRef}
                            className="h-full p-4"
                            style={{ maxHeight: 'calc(100vh - 12rem)' }}
                        >
                            <div className="space-y-4 pb-4">
                                {chatHistory.length === 0 && (
                                    <div className="text-center text-gray-500 py-10">
                                        Start a conversation by asking a question about Damian!
                                    </div>
                                )}

                                {chatHistory.map((msg, index) => (
                                    <div
                                        key={`msg-${index}`}
                                        className={`flex ${msg.type === 'user' ? 'justify-end' : 'justify-start'}`}
                                    >
                                        <div
                                            className={`max-w-[80%] p-3 rounded-lg ${
                                                msg.type === 'user'
                                                    ? 'bg-blue-500 text-white rounded-br-none'
                                                    : 'bg-gray-200 text-gray-800 rounded-bl-none'
                                            }`}
                                        >
                                            {msg.content}
                                        </div>
                                    </div>
                                ))}

                                {processing && (
                                    <div className="flex justify-start">
                                        <div className="max-w-[80%] p-3 rounded-lg bg-gray-200 text-gray-800 rounded-bl-none">
                                            <div className="flex space-x-2">
                                                <div className="w-2 h-2 rounded-full bg-gray-400 animate-bounce" style={{ animationDelay: '0ms' }}></div>
                                                <div className="w-2 h-2 rounded-full bg-gray-400 animate-bounce" style={{ animationDelay: '200ms' }}></div>
                                                <div className="w-2 h-2 rounded-full bg-gray-400 animate-bounce" style={{ animationDelay: '400ms' }}></div>
                                            </div>
                                        </div>
                                    </div>
                                )}

                                <div ref={messagesEndRef} />
                            </div>
                        </ScrollArea>
                    </div>

                    {/* Input */}
                    <CardFooter className="border-t pt-4 p-4 mt-auto shrink-0">
                        <form onSubmit={handleSubmit} className="w-full">
                            <div className="flex space-x-2">
                                <Input
                                    type="text"
                                    placeholder="Ask about Damian..."
                                    className="w-full"
                                    value={data.message}
                                    onChange={e => setData('message', e.target.value)}
                                    disabled={processing}
                                />
                                <Button
                                    type="submit"
                                    disabled={processing || !data.message.trim()}
                                >
                                    {processing ? 'Sending...' : 'Send'}
                                </Button>
                            </div>

                            {errors.message && (
                                <Alert variant="destructive" className="mt-2 py-2">
                                    <AlertDescription>{errors.message}</AlertDescription>
                                </Alert>
                            )}
                        </form>
                    </CardFooter>
                </Card>
            </div>
        </>
    );
}
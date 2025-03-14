import React, { useEffect, useRef } from 'react';
import { Head, usePage } from '@inertiajs/react';
import { useForm } from '@inertiajs/react';
import { router } from '@inertiajs/react';
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Card, CardHeader, CardTitle, CardFooter } from "@/components/ui/card";
import { Alert, AlertDescription } from "@/components/ui/alert";
import { ScrollArea } from "@/components/ui/scroll-area";

export default function Welcome({ chatHistory = [] }) {
    const { flash } = usePage().props;
    const messagesEndRef = useRef(null);
    const scrollAreaRef = useRef(null);
    const videoRef = useRef(null);

    const { data, setData, post, processing, errors, reset } = useForm({
        message: '',
    });

    const scrollToBottom = () => {
        if (messagesEndRef.current) {
            // Use setTimeout to ensure scrolling happens after render
            setTimeout(() => {
                if (messagesEndRef.current) {
                    messagesEndRef.current.scrollIntoView({
                        behavior: 'smooth',
                        block: 'end'
                    });
                }
            }, 100);
        }
    };

    useEffect(() => {
        scrollToBottom();
    }, [chatHistory]);

    useEffect(() => {
        // Autoplay when component mounts
        if (videoRef.current) {
            videoRef.current.play().catch(e => console.log("Auto-play prevented:", e));
        }

        const handleResize = () => {
            // recalculate scroll pos if window resizes
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
                <link href="https://fonts.googleapis.com/css2?family=Knewave&display=swap" rel="stylesheet" />
            </Head>

            <div className="min-h-screen w-full flex flex-col items-center justify-start bg-[#E8F1F2] p-4 pt-8">
                <div className="w-full max-w-2xl flex flex-col items-center">
                    <div className="w-full flex flex-col items-center justify-center -mt-10 mb-10">
                        <div className="flex flex-col md:relative md:w-80 md:h-60">
                            <div className="hidden md:block w-full max-w-xs mx-auto mb-2 md:mb-0 md:absolute md:w-50 md:top-15 md:-left-30 md:z-10">
                                <video
                                    ref={videoRef}
                                    src="/videos/skate.webm"
                                    className="w-full rounded scale-x-[-1]"
                                    muted
                                    loop
                                    playsInline
                                />
                            </div>

                            <div className="hidden md:block text-center md:text-left font-['Knewave'] text-5xl md:text-6xl text-[#001A23] md:absolute md:left-10 md:top-12 md:z-0 leading-tight">
                                DAMIAN<br/>ERMANNI
                            </div>
                        </div>
                    </div>

                    <Card className="w-full shadow-lg">
                        <CardHeader className="border-b flex flex-row justify-between items-center pb-1 2xl:pb-4">
                            <CardTitle className="text-l font-semibold">Hey! Ich bin Damians AI. Was möchtest Du wissen?</CardTitle>
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

                        <div className="h-70 2xl:h-150 overflow-hidden">
                            <ScrollArea
                                ref={scrollAreaRef}
                                className="h-full p-4 text-sm"
                            >
                                <div className="space-y-4 pb-4">
                                    {chatHistory.length === 0 && (
                                        <div className="text-center text-gray-500 py-10">
                                            Beginne ein Gespräch, indem du eine Frage über Real Life Damian stellst! <br/>
                                            AI Damian wird dir antworten. <br/><br/><br/><br/>
                                            * Es werden keine persönlichen Daten gespeichert.
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
                                                        ? 'bg-[#fecd2f] text-gray-800 rounded-br-none'
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

                        <CardFooter className="border-t pt-4 2xl:p-4">
                            <form onSubmit={handleSubmit} className="w-full">
                                <div className="flex space-x-2">
                                    <Input
                                        type="text"
                                        placeholder="Frag mich..."
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

                                {flash.error && (
                                    <Alert variant="destructive" className="mt-2 py-2">
                                        <AlertDescription>{flash.error}</AlertDescription>
                                    </Alert>
                                )}
                            </form>
                        </CardFooter>
                    </Card>
                </div>

                <div className="mt-5 2xl:mt-15">
                    <a
                        href="https://github.com/cvtmal/application"
                        target="_blank"
                        rel="noopener noreferrer"
                        className="mt-4 text-[#001A23] hover:scale-110 transition-transform"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 496 512" className="w-8 h-8">
                            <path d="M165.9 397.4c0 2-2.3 3.6-5.2 3.6-3.3.3-5.6-1.3-5.6-3.6 0-2 2.3-3.6 5.2-3.6 3-.3 5.6 1.3 5.6 3.6zm-31.1-4.5c-.7 2 1.3 4.3 4.3 4.9 2.6 1 5.6 0 6.2-2s-1.3-4.3-4.3-5.2c-2.6-.7-5.5.3-6.2 2.3zm44.2-1.7c-2.9.7-4.9 2.6-4.6 4.9.3 2 2.9 3.3 5.9 2.6 2.9-.7 4.9-2.6 4.6-4.6-.3-1.9-3-3.2-5.9-2.9zM244.8 8C106.1 8 0 113.3 0 252c0 110.9 69.8 205.8 169.5 239.2 12.8 2.3 17.3-5.6 17.3-12.1 0-6.2-.3-40.4-.3-61.4 0 0-70 15-84.7-29.8 0 0-11.4-29.1-27.8-36.6 0 0-22.9-15.7 1.6-15.4 0 0 24.9 2 38.6 25.8 21.9 38.6 58.6 27.5 72.9 20.9 2.3-16 8.8-27.1 16-33.7-55.9-6.2-112.3-14.3-112.3-110.5 0-27.5 7.6-41.3 23.6-58.9-2.6-6.5-11.1-33.3 2.6-67.9 20.9-6.5 69 27 69 27 20-5.6 41.5-8.5 62.8-8.5s42.8 2.9 62.8 8.5c0 0 48.1-33.6 69-27 13.7 34.7 5.2 61.4 2.6 67.9 16 17.7 25.8 31.5 25.8 58.9 0 96.5-58.9 104.2-114.8 110.5 9.2 7.9 17 22.9 17 46.4 0 33.7-.3 75.4-.3 83.6 0 6.5 4.6 14.4 17.3 12.1C428.2 457.8 496 362.9 496 252 496 113.3 383.5 8 244.8 8zM97.2 352.9c-1.3 1-1 3.3.7 5.2 1.6 1.6 3.9 2.3 5.2 1 1.3-1 1-3.3-.7-5.2-1.6-1.6-3.9-2.3-5.2-1zm-10.8-8.1c-.7 1.3.3 2.9 2.3 3.9 1.6 1 3.6.7 4.3-.7.7-1.3-.3-2.9-2.3-3.9-2-.6-3.6-.3-4.3.7zm32.4 35.6c-1.6 1.3-1 4.3 1.3 6.2 2.3 2.3 5.2 2.6 6.5 1 1.3-1.3.7-4.3-1.3-6.2-2.2-2.3-5.2-2.6-6.5-1zm-11.4-14.7c-1.6 1-1.6 3.6 0 5.9 1.6 2.3 4.3 3.3 5.6 2.3 1.6-1.3 1.6-3.9 0-6.2-1.4-2.3-4-3.3-5.6-2z"/>
                        </svg>
                    </a>
                </div>
            </div>
        </>
    );
}
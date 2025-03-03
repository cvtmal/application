import { Head, usePage } from '@inertiajs/react';
import { useForm } from '@inertiajs/react';
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Card, CardContent, CardHeader, CardTitle, CardFooter } from "@/components/ui/card";
import { Alert, AlertDescription } from "@/components/ui/alert";

export default function Welcome() {
    // Get flash messages from the session
    const { flash } = usePage().props;

    // Initialize the form using Inertia's useForm hook
    const { data, setData, post, processing, errors, reset } = useForm({
        message: '',
    });

    function handleSubmit(e) {
        e.preventDefault();
        post('/submit-message', {
            preserveScroll: true,
            onSuccess: () => {
                // Reset the message input after successful submission
                reset('message');
            },
        });
    }

    return (
        <>
            <Head title="Welcome">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
            </Head>
            <div className="min-h-screen w-full flex items-center justify-center bg-blue-50">
                <Card className="w-full max-w-md">
                    <CardHeader>
                        <CardTitle className="text-xl font-semibold text-center">Chat Form</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={handleSubmit} className="space-y-4">
                            <div className="space-y-2">
                                <Input
                                    type="text"
                                    placeholder="Enter your message here"
                                    className="w-full"
                                    value={data.message}
                                    onChange={e => setData('message', e.target.value)}
                                />
                                {errors.message && (
                                    <Alert variant="destructive" className="mt-2 py-2">
                                        <AlertDescription>{errors.message}</AlertDescription>
                                    </Alert>
                                )}
                            </div>
                            <div className="pt-2">
                                <Button
                                    type="submit"
                                    className="w-full"
                                    disabled={processing}
                                >
                                    {processing ? 'Submitting...' : 'Submit'}
                                </Button>
                            </div>
                        </form>
                    </CardContent>

                    {/* Display the response from the server */}
                    {flash.response && (
                        <CardFooter className="border-t pt-4">
                            <div className="w-full">
                                {flash.success && (
                                    <Alert className="mb-3 bg-green-50 text-green-600 border-green-200">
                                        <AlertDescription>{flash.success}</AlertDescription>
                                    </Alert>
                                )}
                                <div className="p-3 bg-slate-50 rounded-md mt-2">
                                    <p className="text-gray-700">{flash.response}</p>
                                </div>
                            </div>
                        </CardFooter>
                    )}
                </Card>
            </div>
        </>
    );
}